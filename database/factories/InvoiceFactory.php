<?php

namespace Database\Factories;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->date();

        return [
            'customer_id' => Customer::factory(),
            'start_date' => $startDate,
            'end_date' => Carbon::make($startDate)->addMonth(),
            'total_cost' => fake()->numberBetween(50, 200)
        ];
    }
}
