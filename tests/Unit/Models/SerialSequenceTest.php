<?php

declare(strict_types=1);

use Mawuva\LaravelSerialSequence\Models\SerialSequence;

it('can create a serial sequence', function () {
    $serialSequence = SerialSequence::factory()->create([
        'serie' => 'TEST',
        'year' => 2024,
        'month' => 1,
        'last_number' => 100,
    ]);

    expect($serialSequence)->toBeInstanceOf(SerialSequence::class);
    expect($serialSequence->serie)->toBe('TEST');
    expect($serialSequence->year)->toBe(2024);
    expect($serialSequence->month)->toBe(1);
    expect($serialSequence->last_number)->toBe(100);
    expect($serialSequence->uuid)->not->toBeNull();
});

it('enforces unique constraint on serie, year and month', function () {
    $data = [
        'serie' => 'INV',
        'year' => 2024,
        'month' => 12,
        'last_number' => 1,
    ];

    SerialSequence::factory()->create($data);

    expect(fn () => SerialSequence::factory()->create($data))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

it('has default value for last_number', function () {
    $serialSequence = SerialSequence::factory()->create([
        'serie' => 'TEST',
        'year' => 2024,
        'month' => 1,
        // last_number non spécifié
    ]);

    expect($serialSequence->last_number)->toBe(0);
});

it('validates serie length limit', function () {
    $serialSequence = SerialSequence::factory()->create([
        'serie' => str_repeat('A', 10), // 10 caractères
        'year' => 2024,
        'month' => 1,
    ]);

    expect($serialSequence->serie)->toHaveLength(10);
});
