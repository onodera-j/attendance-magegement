<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        User::create([
            "name" => "管理者",
            "email" => "admin@example.com",
            "password" => Hash::make("00000000"),
            "status" => 0,
            "email_verified_at" => now(),
            "is_admin" => 1,
        ]);

        User::create([
            "name" => "一般ユーザー",
            "email" => "user@example.com",
            "password" => Hash::make("00000000"),
            "status" => 0,
            "email_verified_at" => now(),
        ]);
    }
}
