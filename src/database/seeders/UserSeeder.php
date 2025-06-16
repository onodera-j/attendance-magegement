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
            "name" => "ユーザー1",
            "email" => "abc@example.com",
            "password" => Hash::make("00000000"),
            "status" => 0,
            "email_verified_at" => now(),
        ]);

        User::create([
            "name" => "一般ユーザー",
            "email" => "user@example.com",
            "password" => Hash::make("00000000"),
            "status" => 0,
            "email_verified_at" => now(),
        ]);

        User::create([
            "name" => "ずんだ",
            "email" => "test@example.com",
            "password" => Hash::make("00000000"),
            "status" => 0,
            "email_verified_at" => now(),
        ]);

        Attendance::create([
            "user_id" => 3,
            "work_start_datetime" =>"2025-05-24 03:20:00",
            "work_end_datetime" =>"2025-05-24 03:50:00",
            "pending" =>"0",
        ]);

        Rest::create([
            "user_id" => 3,
            "attendance_id" => 1,
            "rest_start_datetime" =>"2025-05-24 03:30:00",
            "rest_end_datetime" =>"2025-05-24 03:40:00",
            "rest_time" =>10,
            "rest_status" =>0,
        ]);
    }
}
