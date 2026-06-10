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
            if (!Schema::hasColumn('users', 'name_card')) {
                $table->string('name_card')->nullable()->after('address')->comment('Tên trên CMND/CCCD');
            }

            if (!Schema::hasColumn('users', 'number_card')) {
                $table->string('number_card')->nullable()->after('name_card')->comment('Số CMND/CCCD');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'number_card')) {
                $table->dropColumn('number_card');
            }

            if (Schema::hasColumn('users', 'name_card')) {
                $table->dropColumn('name_card');
            }
        });
    }
};
