<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Admin::create([
            "name" => "管理者",
            "email" => "admin@example.com",
            "password" => Hash::make("00000000"),
            'email_verified_at' => now(),
        ]);
    }
}
