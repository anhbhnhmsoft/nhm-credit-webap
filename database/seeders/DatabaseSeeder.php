<?php

namespace Database\Seeders;

use App\Models\User;
use App\Utils\Constants\Language;
use App\Utils\Constants\RoleUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Tạo user admin
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('Test12345678@'),
            'role' => RoleUser::ADMIN->value,
            'phone' => '0123456789',
            'address' => 'Hà Nội, Việt Nam',
            'email_verified_at' => now()
        ]);

    }
}
