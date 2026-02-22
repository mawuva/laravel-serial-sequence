<?php

declare(strict_types=1);

namespace Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Models\Invoice;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Tests\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'customer_id' => $this->faker->randomNumber(3),
            'status' => $this->faker->randomElement(['draft', 'sent', 'paid', 'cancelled']),
        ];
    }
}
