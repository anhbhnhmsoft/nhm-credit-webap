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
        Schema::table('users', function (Blueprint $table) {
            $table->text('front_image_card')->nullable()->comment('Đường dẫn ảnh mặt trước CMND/CCCD');
            $table->text('back_image_card')->nullable()->comment('Đường dẫn ảnh mặt sau CMND/CCCD');
            $table->text('id_card_selfie_path')->nullable()->comment('Đường dẫn ảnh selfie CMND/CCCD');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('id_card_selfie_path');
            $table->dropColumn('back_image_card');
            $table->dropColumn('front_image_card');
        });
    }
};
