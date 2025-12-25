<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'type' => $this->faker->randomElement(['Income', 'Expense']),
            'status' => $this->faker->randomElement(['Completed', 'Pending']),
            'category' => $this->faker->randomElement(['Food', 'Transport', 'Utilities', 'Entertainment', 'Salary', 'Freelance', 'Savings']),
            'user_id' => User::factory(),
        ];
    }
}
