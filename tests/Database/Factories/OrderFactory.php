<?php

declare(strict_types=1);

namespace Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Models\Order;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Tests\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'total' => $this->faker->randomFloat(2, 50, 5000),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'shipped', 'delivered', 'cancelled']),
            'customer_id' => $this->faker->randomNumber(3),
        ];
    }
}
