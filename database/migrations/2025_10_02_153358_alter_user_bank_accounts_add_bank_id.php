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
        Schema::table('user_bank_accounts', function (Blueprint $table) {
            $table->foreignId('bank_id')->after('user_id')
          ->nullable()->constrained('banks')->nullOnDelete();
          $table->dropColumn('bank_code');
          $table->dropColumn('bank_name');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_bank_accounts', function (Blueprint $table) {
            $table->dropForeign(['bank_id']);
            $table->dropColumn('bank_id');
            $table->string('bank_code')->after('user_id');
            $table->string('bank_name')->after('bank_code');
        });
    }
};
