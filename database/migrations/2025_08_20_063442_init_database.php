<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
		Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Họ và tên người dùng');
            $table->string('email')->unique()->comment('Email đăng nhập');
            $table->string('phone')->nullable()->comment('Số điện thoại');
            $table->string('address')->nullable()->comment('Địa chỉ');
			$table->string('name_card')->nullable()->comment('Tên trên thẻ');
            $table->string('number_card')->nullable()->unique()->comment('Số thẻ');
            $table->string('front_image_card')->nullable()->comment('Ảnh mặt trước thẻ');
            $table->string('back_image_card')->nullable()->comment('Ảnh mặt sau thẻ');
            $table->text('introduce')->nullable()->comment('Giới thiệu bản thân');
            $table->tinyInteger('role')->comment('Vai trò người dùng, lưu trong enum RoleUser');
            $table->string('avatar_path')->nullable()->comment('Đường dẫn ảnh đại diện');
            $table->timestamp('email_verified_at')->nullable()->comment('Thời gian xác thực email');
            $table->timestamp('phone_verified_at')->nullable()->comment('Thời gian xác thực số điện thoại');
            $table->string('password')->comment('Mật khẩu đã mã hóa');
            $table->unique('phone')->comment('Số điện thoại duy nhất');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });


        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });

		Schema::create('user_reset_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('code', 6);
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

		// Tạo bảng configs để lưu trữ các cấu hình hệ thống
		Schema::create('configs', function (Blueprint $table) {
			$table->id();
			$table->string('config_key')->unique();
			$table->string('config_type')->nullable();
			$table->text('config_value');
			$table->text('description')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});

		Schema::create('banks', function (Blueprint $table) {
			$table->id();
			$table->string('code')->comment('Mã ngân hàng');
			$table->string('name')->comment('Tên ngân hàng');
			$table->softDeletes();
			$table->timestamps();
		});
		// Tài khoản ngân hàng của người dùng
		Schema::create('user_bank_accounts', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('ID người dùng');
			$table->foreignId('bank_id')->nullable()->constrained('banks')->nullOnDelete()->comment('ID ngân hàng');
			$table->string('account_number')->comment('Số tài khoản');
			$table->string('account_name')->comment('Tên chủ tài khoản');
			$table->boolean('is_verified')->default(false)->comment('Đã xác thực');
			$table->softDeletes();
			$table->timestamps();
			$table->unique(['user_id','account_number'])->comment('Mỗi user chỉ có 1 tài khoản duy nhất');
		});

		// Bảng để lưu trữ các gói vay
		Schema::create('loan_packages', function (Blueprint $table) {
			$table->id();
            $table->json('config_loans')->comment('Cấu hình gói vay');
			$table->softDeletes();
			$table->timestamps();
		});

		// Bảng để lưu trữ các khoản vay của người dùng
		Schema::create('user_loans', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('ID người dùng');
			$table->foreignId('loan_package_id')->nullable()->constrained()->nullOnDelete()->comment('ID gói vay');
			$table->unsignedBigInteger('principal_amount')->comment('Số tiền gốc vay');
			$table->unsignedSmallInteger('term_months')->comment('Kỳ hạn vay (tháng)');
			$table->decimal('interest_rate_year', 5, 2)->comment('Lãi suất năm (%)');
			$table->decimal('service_fee_amount', 12, 2)->default(0)->comment('Phí dịch vụ (VND)');
			$table->decimal('disbursed_amount', 12, 2)->default(0)->comment('Số tiền đã giải ngân');
			$table->date('start_date')->nullable()->comment('Ngày bắt đầu vay');
			$table->date('due_date')->nullable()->comment('Ngày đến hạn');
			$table->decimal('total_due_amount', 12, 2)->default(0)->comment('Tổng số tiền phải trả');
			$table->decimal('total_paid_amount', 12, 2)->default(0)->comment('Tổng số tiền đã trả');
			$table->tinyInteger('status')->default(1)->comment('Trạng thái khoản vay, lưu trong enum LoanStatus');
			$table->text('reject_reason')->nullable()->comment('Lý do từ chối');
			$table->softDeletes();
			$table->timestamps();
		});

		// Bảng để lưu trữ lịch trả nợ
		Schema::create('user_loan_logs', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_loan_id')->constrained('user_loans')->cascadeOnDelete()->comment('ID khoản vay');
			$table->unsignedSmallInteger('installment_no')->comment('Số kỳ trả');
			$table->date('due_date')->comment('Ngày đến hạn trả');
			$table->date('actual_due_date')->nullable()->comment('Ngày thực nhận trả tiền');
			$table->decimal('principal_due', 12, 2)->default(0)->comment('Gốc phải trả');
			$table->decimal('interest_due', 12, 2)->default(0)->comment('Lãi phải trả');
			$table->decimal('fee_due', 12, 2)->default(0)->comment('Phí phải trả');
			$table->decimal('total_paid', 12, 2)->default(0)->comment('Tổng đã trả');
			$table->tinyInteger('status')->default(1)->comment('Trạng thái kỳ trả, lưu trong enum LoanLogStatus');
			$table->softDeletes();
			$table->timestamps();
			$table->unique(['user_loan_id','installment_no'])->comment('Mỗi khoản vay chỉ có 1 kỳ trả duy nhất');
		});

		// Tạo bảng payments để lưu trữ các giao dịch thanh toán
		Schema::create('payments', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('ID người dùng');
			$table->foreignId('user_loan_id')->nullable()->constrained('user_loans')->nullOnDelete()->comment('ID khoản vay');
			$table->foreignId('user_loan_log_id')->nullable()->constrained('user_loan_logs')->nullOnDelete()->comment('ID kỳ trả');
			$table->string('transaction_code')->nullable()->comment('Mã giao dịch');
			$table->decimal('amount', 12, 2)->comment('Số tiền giao dịch');
			$table->tinyInteger('direction')->default(1)->comment('Chiều giao dịch, lưu trong enum PaymentDirection');
			$table->tinyInteger('status')->default(1)->comment('Trạng thái giao dịch, lưu trong enum PaymentStatus');
			$table->json('metadata')->nullable()->comment('Dữ liệu bổ sung');
			$table->softDeletes();
			$table->timestamps();
			$table->index(['user_loan_id','user_loan_log_id'])->comment('Index cho truy vấn nhanh');
		});

		// Tạo bảng user_notifications để lưu trữ thông báo người dùng
		Schema::create('user_notifications', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('ID người dùng');
			$table->string('title')->comment('Tiêu đề thông báo');
			$table->text('description')->nullable()->comment('Nội dung thông báo');
			$table->tinyInteger('notification_type')->default(1)->comment('Loại thông báo, lưu trong enum NotificationType');
			$table->tinyInteger('status')->default(1)->comment('Trạng thái thông báo, lưu trong enum NotificationStatus');
			$table->json('data')->nullable()->comment('Dữ liệu bổ sung');
			$table->softDeletes();
			$table->timestamps();
		});
    }
    /**
     * Reverse the migrations.
     */
	public function down(): void
	{
		Schema::dropIfExists('sessions');
		Schema::dropIfExists('user_notifications');
		Schema::dropIfExists('payments');
		Schema::dropIfExists('user_loan_logs');
		Schema::dropIfExists('user_loans');
		Schema::dropIfExists('loan_packages');
		Schema::dropIfExists('user_bank_accounts');
		Schema::dropIfExists('banks');
		Schema::dropIfExists('configs');
		Schema::dropIfExists('personal_access_tokens');
		Schema::dropIfExists('user_reset_codes');
		Schema::dropIfExists('users');
		Schema::dropIfExists('sessions');
	}
};
