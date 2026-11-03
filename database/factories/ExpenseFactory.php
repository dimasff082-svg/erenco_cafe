<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'expense_number' => 'EXP'.$this->faker->unique()->numberBetween(100, 999),
            'expense_type' => $this->faker->randomElement(['raw_material', 'operational']),
            'title' => $this->faker->words(3, true),
            'category' => 'Bahan Roti',
            'amount' => $this->faker->numberBetween(50000, 1000000),
            'expense_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'receipt_proof' => null,
            'notes' => null,
            'user_id' => User::factory(),
        ];
    }
}
