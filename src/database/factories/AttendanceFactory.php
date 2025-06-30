<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;


class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "user_id" => null,
            "work_start_datetime" => null,
            "work_end_datetime" => null,
            "work_time" => 0,
            "remarks" => null,
            "pending" =>0,
        ];
    }
}
