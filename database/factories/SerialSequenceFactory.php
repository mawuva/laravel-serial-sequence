<?php

declare(strict_types=1);

namespace Mawuva\LaravelSerialSequence\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mawuva\LaravelSerialSequence\Models\SerialSequence;

class SerialSequenceFactory extends Factory
{
    protected $model = SerialSequence::class;

    public function definition(): array
    {
        return [
            'serie' => $this->faker->word(),
            'year' => $this->faker->year(),
            'month' => $this->faker->month(),
            'last_number' => 0, // Valeur par défaut comme dans la migration
        ];
    }
}
