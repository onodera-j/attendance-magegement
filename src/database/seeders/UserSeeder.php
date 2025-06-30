<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Rest;
use App\Models\Attendance;

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
            "name" => "一般ログイン",
            "email" => "test@example.com",
            "password" => Hash::make("00000000"),
            "status" => 0,
            "email_verified_at" => now(),
        ]);

    }
}
