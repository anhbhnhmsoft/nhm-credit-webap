<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Bank;
use App\Models\Config;
use App\Models\User;
use App\Utils\Constants\RoleUser;
use Illuminate\Support\Facades\Hash;
use App\Models\PageStatic;
use App\Utils\Constants\CommonStatus;
use App\Utils\Constants\ConfigName;
use App\Utils\Constants\ConfigType;
use App\Utils\Constants\PageStaticType;
use App\Utils\Constants\StoragePath;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InitApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:init-application';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Khởi tạo ứng dụng với các thiết lập ban đầu';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $migrateCode = Artisan::call('migrate');
        $this->info('--- Khởi tạo database');

        if ($migrateCode === Command::SUCCESS) {
            $this->info('Lệnh migrate đã thành công!');
        } else {
            $this->error('Lỗi khi chạy migrate!');
            return Command::FAILURE;
        }

        $this->info('--- Khởi tạo admin user!');
        User::query()->create([
            'name' => 'admin',
            'phone' => '0333333333',
            'email' => 'admin@admin.com',
            'role' => RoleUser::ADMIN->value,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'password' => Hash::make('Test1234568@'),
        ]);

        $this->info('--- Seeding demo database');
        DB::beginTransaction();
        
        $r1 = $this->seedingBank();
        if (!$r1) {
            DB::rollBack();
            $this->error('Lỗi khi chạy Seeding demo database r1!');
            return Command::FAILURE;
        }

        $r2 = $this->seedingPageStatic();
        if (!$r2) {
            DB::rollBack();
            $this->error('Lỗi khi chạy Seeding demo database r2!');
            return Command::FAILURE;
        }

        $r3 = $this->seedingConfig();
        if (!$r3) {
            DB::rollBack();
            $this->error('Lỗi khi chạy Seeding demo database r3!');
            return Command::FAILURE;
        }

        DB::commit();
        $this->info('Seeding database thành công');
        return Command::SUCCESS;
    }

    private function seedingBank(): bool
    {
        try {
            // Lấy danh sách ngân hàng từ VietQR
            $this->info('Tải danh sách ngân hàng từ VietQR...');
            $res = Http::timeout(20)->get('https://api.vietqr.io/v2/banks');
            if ($res->ok()) {
                $data = $res->json('data') ?? [];
                foreach ($data as $item) {
                    $code = $item['code'] ?? null;
                    $name = $item['name'] ?? ($item['shortName'] ?? null);
                    if (! $code || ! $name) {
                        continue;
                    }
                    Bank::updateOrCreate(['code' => $code], ['name' => $name]);
                }
                $this->info('Seed ngân hàng: thành công');
                return true;
            } else {
                $this->warn('Không thể tải danh sách ngân hàng từ VietQR. Bỏ qua.');
                return true;
            }
        } catch (\Throwable $e) {
            $this->warn('Seed ngân hàng lỗi: ' . $e->getMessage());
            return false;
        }
    }

    private function seedingPageStatic(): bool
    {
        try {
            $pages = [
                [
                    'title' => 'Trung tâm trợ giúp',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">  <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" /></svg>',
                    'slug' => 'trung-tam-tro-giup',
                    'content' => '<p># <strong>Trung Tâm Hỗ Trợ</strong></p><p>Chào mừng bạn đến với Trung Tâm Hỗ Trợ! Dưới đây là các câu hỏi thường gặp, bài viết hướng dẫn và thông tin liên hệ để giúp bạn giải quyết vấn đề nhanh chóng.</p><p>---</p><p>## <strong>Câu Hỏi Thường Gặp</strong></p><p>### <strong>1. Làm thế nào để thay đổi mật khẩu?</strong></p><p>Để thay đổi mật khẩu, bạn cần truy cập vào phần <strong>Cài đặt tài khoản</strong> của mình. Sau đó chọn <strong>&quot;Thay đổi mật khẩu&quot;</strong> và làm theo hướng dẫn trên màn hình.</p><p>### <strong>2. Làm thế nào để liên hệ với bộ phận hỗ trợ?</strong></p><p>Bạn có thể liên hệ với bộ phận hỗ trợ của chúng tôi qua email <a target="_blank" rel="noopener noreferrer nofollow" href="mailto:support@example.com"><strong>support@example.com</strong></a> hoặc gọi điện thoại đến số <strong>123-456-7890</strong>.</p><p>### <strong>3. Sản phẩm của tôi bị lỗi, tôi phải làm gì?</strong></p><p>Nếu sản phẩm của bạn gặp sự cố, vui lòng liên hệ với chúng tôi để yêu cầu <strong>bảo hành</strong> hoặc <strong>đổi trả</strong>. Đảm bảo bạn cung cấp thông tin về sản phẩm và mô tả chi tiết về sự cố.</p><p>---</p><p>## <strong>Bài Viết Hướng Dẫn</strong></p><p>### <strong>1. Hướng dẫn sử dụng tính năng tìm kiếm sản phẩm</strong></p><p>Tính năng tìm kiếm giúp bạn dễ dàng tìm thấy sản phẩm yêu thích. Để sử dụng, chỉ cần nhập tên sản phẩm vào thanh tìm kiếm ở góc trên bên phải của trang và nhấn <strong>Enter</strong>.</p><p>[Đọc thêm...](#)</p><p>### <strong>2. Cách theo dõi đơn hàng của bạn</strong></p><p>Để theo dõi đơn hàng, bạn cần đăng nhập vào tài khoản của mình, sau đó vào mục <strong>&quot;Đơn hàng&quot;</strong> để kiểm tra trạng thái và tiến trình giao hàng của đơn hàng.</p><p>[Đọc thêm...](#)</p><p>---</p><p>## <strong>Liên Hệ Hỗ Trợ</strong></p><p>Nếu bạn cần hỗ trợ thêm, đừng ngần ngại liên hệ với chúng tôi qua các kênh sau:</p><p>- <strong>Email</strong>: [<a target="_blank" rel="noopener noreferrer nofollow" href="mailto:support@example.com">support@example.com</a>](<a target="_blank" rel="noopener noreferrer nofollow" href="mailto:support@example.com">mailto:support@example.com</a>)</p><p>- <strong>Điện thoại</strong>: <strong>123-456-7890</strong></p><p>- <strong>Chat trực tuyến</strong> (tích hợp trên website của chúng tôi)</p><p></p>',
                ],
                [
                    'title' => 'Chính sách bảo mật',
                    'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>',
                    'slug' => 'chinh-sach-bao-mat',
                    'content' => '<h1><strong>Chính sách Bảo mật</strong></h1><ol start="1"><li><p><strong>Chấp thuận của Bạn đối với Chính sách Quyền riêng tư này</strong></p><ol start="1"><li><p><strong>Đồng ý với Chính sách:</strong> Khi Bạn sử dụng, truy cập hoặc tương tác với Ứng dụng của chúng tôi, Bạn đồng ý với Chính sách Quyền riêng tư của chúng tôi.</p></li><li><p><strong>Cơ sở pháp lý:</strong> Chấp thuận của Bạn chính là cơ sở pháp lý để chúng tôi thu thập, sử dụng, lưu trữ và xử lý Thông tin cá nhân của Bạn.</p></li><li><p><strong>Sử dụng dịch vụ bên thứ ba:</strong> Bạn thừa nhận rằng khi đồng ý với Chính sách này, Bạn đang sử dụng Ứng dụng tích hợp với các dịch vụ bên thứ ba.</p></li></ol></li><li><p><strong>Phạm vi áp dụng của Chính sách Quyền riêng tư này</strong></p><ol start="1"><li><p><strong>Quy định thu thập thông tin:</strong> Chính sách này quy định cách mà Joot Vay thu thập, sử dụng, bảo vệ và chia sẻ thông tin.</p></li><li><p><strong>Không áp dụng cho bên thứ ba:</strong> Chính sách này không áp dụng cho thông tin thu thập từ ứng dụng bên thứ ba.</p></li></ol></li><li><p><strong>Thông tin mà chúng tôi thu thập</strong></p><ol start="1"><li><p><strong>Thông tin Bạn cung cấp:</strong></p><ol start="1"><li><p><strong>Các phương thức cung cấp:</strong> Bạn đồng ý cung cấp thông tin qua các biểu mẫu và tính năng trên Ứng dụng.</p></li><li><p><strong>Thông tin cá nhân:</strong> Thông tin có thể nhận dạng Bạn như tên, địa chỉ, email.</p></li><li><p><strong>Thông tin phi cá nhân:</strong> Thông tin không xác định Bạn cá nhân như giới tính.</p></li></ol></li><li><p><strong>Thông tin tự động thu thập:</strong></p><ol start="1"><li><p><strong>Thông tin sử dụng:</strong> Chúng tôi tự động thu thập thông tin về cách Bạn truy cập Ứng dụng.</p></li><li><p><strong>Thông tin thiết bị:</strong> Thông tin về thiết bị Bạn sử dụng để truy cập Ứng dụng Joot Vay</p></li></ol></li><li><p><strong>Thông tin từ bên thứ ba:</strong> Chúng tôi có thể thu thập thông tin từ các đối tác bên thứ ba.</p></li></ol></li><li><p><strong>Sử dụng thông tin mà chúng tôi thu thập</strong></p><ol start="1"><li><p><strong>Mục đích sử dụng:</strong> Chúng tôi sử dụng thông tin để vận hành Ứng dụng và cải thiện dịch vụ.</p></li><li><p><strong>Ví dụ về sử dụng:</strong></p><ol start="1"><li><p><strong>Tạo Tài khoản:</strong> Để tạo và bảo mật Tài khoản người dùng.</p></li><li><p><strong>Cải thiện đề xuất:</strong> Điều chỉnh nội dung và đề xuất phù hợp.</p></li></ol></li></ol></li></ol>',
                ]
            ];

            foreach ($pages as $page) {
                $slug = $page['slug'] ?? Str::slug($page['title']);
                PageStatic::query()->updateOrCreate(
                    ['slug' => $slug],
                    [
                        'title'        => $page['title'],
                        'icon_svg'     => $page['icon_svg'],
                        'content'      => $page['content'],
                        'type'         => PageStaticType::FIXED->value,
                        'status'       => CommonStatus::ACTIVE->value,
                    ]
                );
            }

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    private function seedingConfig(): bool
    {
        $logoPath = StoragePath::makePath(StoragePath::CONFIG_PATH, 'logo.jpg');
        Storage::disk('public')->put($logoPath, file_get_contents(public_path('images/logo.jpg')));

        try {
            Config::query()->insert([
                [
                    'config_key' => ConfigName::LOGO->value,
                    'config_type' => ConfigType::IMAGE->value,
                    'config_value' => $logoPath,
                    'description' => 'Cấu hình logo website',
                ],
                [
                    'config_key' => ConfigName::ADMIN_ACCOUNT_BANK_NAME->value,
                    'config_type' => ConfigType::STRING->value,
                    'config_value' => 'MAI VAN HUY',
                    'description' => 'Chú thích: Tên chủ thể ngân hàng chính của hệ thống dùng để thanh toán',
                ],
                [
                    'config_key' => ConfigName::ADMIN_ACCOUNT_BANK_ACCOUNT->value,
                    'config_type' => ConfigType::STRING->value,
                    'config_value' => '0125438569',
                    'description' => 'Chú thích: Số tài khoản ngân hàng chính của hệ thống dùng để thanh toán',
                ],
                [
                    'config_key' => ConfigName::ADMIN_ACCOUNT_BANK_BIN->value,
                    'config_type' => ConfigType::STRING->value,
                    'config_value' => '970422',
                    'description' => 'Chú thích: Mã kiểm tra số tài khoản ngân hàng chính của hệ thống dùng để thanh toán',
                ]
            ]);
            return true;
        }catch (\Exception $exception){
            return false;
        }
    }
}
