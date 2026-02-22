<?php

declare(strict_types=1);

namespace Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Models\Booking;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Tests\Models\Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'client_id' => $this->faker->randomNumber(3),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled']),
            'amount' => $this->faker->randomFloat(2, 100, 2000),
        ];
    }
}
