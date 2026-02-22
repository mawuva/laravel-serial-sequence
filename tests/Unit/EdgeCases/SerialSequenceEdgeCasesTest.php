<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Mawuva\LaravelSerialSequence\Services\SerialGenerator;

beforeEach(function () {
    // Clean up before each test
    \Mawuva\LaravelSerialSequence\Models\SerialSequence::query()->delete();
});

it('automatically resets when year changes', function () {
    $generator = new SerialGenerator;

    // Generate in 2023
    $date2023 = CarbonImmutable::create(2023, 12, 15);
    $serial2023 = $generator->generate('INV', null, $date2023);

    expect($serial2023->year)->toBe(2023);
    expect($serial2023->month)->toBe(12);
    expect($serial2023->number)->toBe(1);

    // Generate in 2024 (different year, same month)
    $date2024 = CarbonImmutable::create(2024, 12, 15);
    $serial2024 = $generator->generate('INV', null, $date2024);

    expect($serial2024->year)->toBe(2024);
    expect($serial2024->month)->toBe(12);
    expect($serial2024->number)->toBe(1); // Reset to 1
});

it('automatically resets when month changes', function () {
    $generator = new SerialGenerator;

    // Generate in January
    $dateJan = CarbonImmutable::create(2024, 1, 15);
    $serialJan = $generator->generate('INV', null, $dateJan);

    expect($serialJan->year)->toBe(2024);
    expect($serialJan->month)->toBe(1);
    expect($serialJan->number)->toBe(1);

    // Generate in February (same year, different month)
    $dateFeb = CarbonImmutable::create(2024, 2, 15);
    $serialFeb = $generator->generate('INV', null, $dateFeb);

    expect($serialFeb->year)->toBe(2024);
    expect($serialFeb->month)->toBe(2);
    expect($serialFeb->number)->toBe(1); // Reset to 1
});

it('maintains separate sequences for different periods', function () {
    $generator = new SerialGenerator;

    $date1 = CarbonImmutable::create(2024, 1, 15);
    $date2 = CarbonImmutable::create(2024, 2, 15);

    // Generate multiple in January
    $serial1a = $generator->generate('INV', null, $date1);
    $serial1b = $generator->generate('INV', null, $date1);

    // Generate in February
    $serial2 = $generator->generate('INV', null, $date2);

    expect($serial1a->number)->toBe(1);
    expect($serial1b->number)->toBe(2);
    expect($serial2->number)->toBe(1); // Reset for new month

    // Generate another in February
    $serial2b = $generator->generate('INV', null, $date2);
    expect($serial2b->number)->toBe(2);
});

it('handles large serial numbers correctly', function () {
    $generator = new SerialGenerator;

    // Create a sequence with high number
    \Mawuva\LaravelSerialSequence\Models\SerialSequence::create([
        'serie' => 'INV',
        'year' => (int) now()->format('Y'),
        'month' => (int) now()->format('m'),
        'last_number' => 99999,
    ]);

    $serialData = $generator->generate('INV');

    expect($serialData->number)->toBe(100000);
    expect($serialData->serial)->toContain('-100000');
});

it('handles very long series names', function () {
    $generator = new SerialGenerator;

    $longSerie = str_repeat('A', 10); // Maximum length
    $serialData = $generator->generate($longSerie);

    expect($serialData->serie)->toBe($longSerie);
    expect($serialData->serial)->toStartWith($longSerie.'-');
});

it('handles year transition correctly', function () {
    $generator = new SerialGenerator;

    // End of year
    $dateDec = CarbonImmutable::create(2023, 12, 31);
    $serialDec = $generator->generate('INV', null, $dateDec);

    // Start of next year
    $dateJan = CarbonImmutable::create(2024, 1, 1);
    $serialJan = $generator->generate('INV', null, $dateJan);

    expect($serialDec->year)->toBe(2023);
    expect($serialDec->month)->toBe(12);
    expect($serialDec->number)->toBe(1);

    expect($serialJan->year)->toBe(2024);
    expect($serialJan->month)->toBe(1);
    expect($serialJan->number)->toBe(1); // Reset for new year
});

it('handles leap year February correctly', function () {
    $generator = new SerialGenerator;

    // February in leap year
    $dateLeap = CarbonImmutable::create(2024, 2, 29); // 2024 is leap year
    $serialLeap = $generator->generate('INV', null, $dateLeap);

    expect($serialLeap->year)->toBe(2024);
    expect($serialLeap->month)->toBe(2);
    expect($serialLeap->number)->toBe(1);

    // February in non-leap year
    $dateNormal = CarbonImmutable::create(2023, 2, 28);
    $serialNormal = $generator->generate('INV', null, $dateNormal);

    expect($serialNormal->year)->toBe(2023);
    expect($serialNormal->month)->toBe(2);
    expect($serialNormal->number)->toBe(1);
});
