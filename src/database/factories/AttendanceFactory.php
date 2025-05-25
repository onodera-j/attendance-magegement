<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;


class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "user_id" => 3,
            "work_start_datetime" =>"2025-05-24 03:20:00",
            "work_end_datetime" =>"2025-05-24 03:20:00",
            "total_time" =>"10",
            "pending" =>"0",
        ];
    }
}
