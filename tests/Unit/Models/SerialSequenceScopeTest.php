<?php

declare(strict_types=1);

use Mawuva\LaravelSerialSequence\Models\SerialSequence;

beforeEach(function () {
    // Clean up before each test
    SerialSequence::query()->delete();
});

it('uses forPeriod scope correctly', function () {
    // Create test sequences
    SerialSequence::create(['serie' => 'INV', 'year' => 2024, 'month' => 1, 'last_number' => 10]);
    SerialSequence::create(['serie' => 'INV', 'year' => 2024, 'month' => 2, 'last_number' => 5]);
    SerialSequence::create(['serie' => 'ORD', 'year' => 2024, 'month' => 1, 'last_number' => 3]);

    // Query for specific period
    $sequences = SerialSequence::forPeriod('INV', 2024, 1)->get();

    expect($sequences)->toHaveCount(1);
    $sequence = $sequences->first();
    expect($sequence->serie)->toBe('INV');
    expect($sequence->year)->toBe(2024);
    expect($sequence->month)->toBe(1);
    expect($sequence->last_number)->toBe(10);
});

it('returns empty when no sequence exists for period', function () {
    $sequences = SerialSequence::forPeriod('NONEXISTENT', 2024, 1)->get();
    expect($sequences)->toHaveCount(0);
});

it('can combine forPeriod with other scopes', function () {
    SerialSequence::create(['serie' => 'INV', 'year' => 2024, 'month' => 1, 'last_number' => 10]);
    SerialSequence::create(['serie' => 'INV', 'year' => 2024, 'month' => 2, 'last_number' => 5]);

    // Combine with where clause
    $sequences = SerialSequence::forPeriod('INV', 2024, 1)
        ->where('last_number', '>', 5)
        ->get();

    expect($sequences)->toHaveCount(1);
    expect($sequences->first()->last_number)->toBe(10);
});
