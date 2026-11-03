<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date' => now()->toDateString(),
            'type' => 'check_in',
            'time' => now()->toTimeString(),
            'latitude' => -5.147665,
            'longitude' => 119.432732,
            'distance_meters' => 15.5,
            'photo_path' => 'attendances/sample.jpg',
            'status' => 'present',
            'notes' => 'Tepat waktu',
        ];
    }
}
