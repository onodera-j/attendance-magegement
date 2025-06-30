<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Rest;
use Carbon\Carbon;


class RestFactory extends Factory
{
    protected $model = Rest::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            'user_id' => null,
            'attendance_id' => null,
            'rest_start_datetime' => null,
            'rest_end_datetime' => null,
            'rest_time' => 0,
            'rest_status' => 0,
        ];
    }
}
