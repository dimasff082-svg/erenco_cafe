<?php

namespace Database\Factories;

use App\Models\Salary;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Salary>
 */
class SalaryFactory extends Factory
{
    protected $model = Salary::class;

    public function definition(): array
    {
        $basic = $this->faker->numberBetween(2500000, 4500000);
        $allowance = $this->faker->numberBetween(200000, 500000);
        $deductions = $this->faker->numberBetween(0, 100000);
        $net = $basic + $allowance - $deductions;

        return [
            'payroll_number' => 'PAY-'.strtoupper($this->faker->bothify('##??###')),
            'user_id' => User::factory(),
            'period_month' => now()->translatedFormat('F Y'),
            'basic_salary' => $basic,
            'allowance' => $allowance,
            'deductions' => $deductions,
            'net_salary' => $net,
            'payment_date' => now()->toDateString(),
            'payment_status' => 'paid',
            'payment_method' => 'transfer',
            'notes' => 'Gaji bulanan reguler',
            'created_by' => null,
        ];
    }
}
