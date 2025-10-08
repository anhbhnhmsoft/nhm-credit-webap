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
        Schema::create('page_statics', function (Blueprint $table) {
            $table->id();
            $table->text('icon_svg')->nullable()->comment('Icon SVG lấy từ Heroicons');
            $table->string('title');
            $table->text('content');
            $table->integer('type')->default(1)->comment('Loại trang tĩnh, lưu trong enum PageStaticType');
            $table->string('slug');
            $table->tinyInteger('status')->comment('Trạng thái, lưu trong enum CommonStatus');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_statics');
    }
};
