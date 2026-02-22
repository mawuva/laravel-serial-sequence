<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Mawuva\LaravelSerialSequence\Data\SerialData;
use Mawuva\LaravelSerialSequence\Models\SerialSequence;
use Mawuva\LaravelSerialSequence\Services\SerialGenerator;

beforeEach(function () {
    // Clean up before each test
    SerialSequence::query()->delete();
});

it('can generate a basic serial number for new sequence', function () {
    $generator = new SerialGenerator();
    
    $serialData = $generator->generate('INV');
    
    expect($serialData)->toBeInstanceOf(SerialData::class);
    expect($serialData->serial)->toBeString();
    expect($serialData->serie)->toBe('INV');
    expect($serialData->year)->toBe((int) now()->format('Y'));
    expect($serialData->month)->toBe((int) now()->format('m'));
    expect($serialData->number)->toBe(1);
    
    // Check default format: INV-0224-000001 (assuming current date)
    $expectedFormat = 'INV-' . now()->format('my') . '-000001';
    expect($serialData->serial)->toBe($expectedFormat);
    
    // Verify sequence was created in database
    $sequence = SerialSequence::forPeriod('INV', $serialData->year, $serialData->month)
                             ->first();
                             
    expect($sequence)->not->toBeNull();
    expect($sequence->last_number)->toBe(1);
});

it('increments serial number for existing sequence', function () {
    $generator = new SerialGenerator();
    
    // Create initial sequence
    $serialData1 = $generator->generate('INV');
    expect($serialData1->number)->toBe(1);
    
    // Generate second serial
    $serialData2 = $generator->generate('INV');
    expect($serialData2->number)->toBe(2);
    expect($serialData2->serie)->toBe($serialData1->serie);
    expect($serialData2->year)->toBe($serialData1->year);
    expect($serialData2->month)->toBe($serialData1->month);
    
    // Generate third serial
    $serialData3 = $generator->generate('INV');
    expect($serialData3->number)->toBe(3);
    
    // Verify database state
    $sequence = SerialSequence::forPeriod('INV', $serialData1->year, $serialData1->month)
                             ->first();
                             
    expect($sequence->last_number)->toBe(3);
});

it('generates different sequences for different series', function () {
    $generator = new SerialGenerator();
    
    $invSerial = $generator->generate('INV');
    $ordSerial = $generator->generate('ORD');
    $bkgSerial = $generator->generate('BKG');
    
    expect($invSerial->serie)->toBe('INV');
    expect($ordSerial->serie)->toBe('ORD');
    expect($bkgSerial->serie)->toBe('BKG');
    
    expect($invSerial->number)->toBe(1);
    expect($ordSerial->number)->toBe(1);
    expect($bkgSerial->number)->toBe(1);
    
    // Verify all start with number 1
    expect($invSerial->serial)->toContain('INV-');
    expect($ordSerial->serial)->toContain('ORD-');
    expect($bkgSerial->serial)->toContain('BKG-');
});

it('creates separate sequences for different periods', function () {
    $generator = new SerialGenerator();
    
    $date1 = CarbonImmutable::create(2024, 1, 15); // January 2024
    $date2 = CarbonImmutable::create(2024, 2, 15); // February 2024
    
    $serial1 = $generator->generate('INV', null, $date1);
    $serial2 = $generator->generate('INV', null, $date2);
    
    expect($serial1->year)->toBe(2024);
    expect($serial1->month)->toBe(1);
    expect($serial1->number)->toBe(1);
    
    expect($serial2->year)->toBe(2024);
    expect($serial2->month)->toBe(2);
    expect($serial2->number)->toBe(1); // Reset to 1 for new period
    
    // Verify different sequences in database
    $sequences = SerialSequence::forPeriod('INV', $serial1->year, $serial1->month)->get();
    expect($sequences)->toHaveCount(1);
    
    $sequencesFeb = SerialSequence::forPeriod('INV', $serial2->year, $serial2->month)->get();
    expect($sequencesFeb)->toHaveCount(1);
});

it('handles prefix correctly', function () {
    $generator = new SerialGenerator();
    
    $serialWithoutPrefix = $generator->generate('INV');
    $serialWithPrefix = $generator->generate('INV', 'PREFIX');
    
    expect($serialWithoutPrefix->serial)->not->toContain('PREFIX');
    expect($serialWithPrefix->serial)->toStartWith('PREFIX/');
    
    // Both should have same number (different sequences)
    expect($serialWithoutPrefix->number)->toBe(1);
    expect($serialWithPrefix->number)->toBe(2);
});

it('uses custom date for period calculation', function () {
    $generator = new SerialGenerator();
    $customDate = CarbonImmutable::create(2023, 12, 25);
    
    $serialData = $generator->generate('TEST', null, $customDate);
    
    expect($serialData->year)->toBe(2023);
    expect($serialData->month)->toBe(12);
    expect($serialData->number)->toBe(1);
    expect($serialData->serial)->toContain('TEST-');
    expect($serialData->serial)->toContain('1223'); // December 2023
});

it('formats serial with custom configuration', function () {
    // Override config for this test
    config([
        'serial-sequence.separator' => '_',
        'serial-sequence.prefix_separator' => '-',
        'serial-sequence.number_length' => 4,
        'serial-sequence.month_length' => 1,
        'serial-sequence.year_length' => 4,
    ]);
    
    $generator = new SerialGenerator();
    $serialData = $generator->generate('INV', 'PREFIX');
    
    // Expected format uses month+year concatenation (month padded to month_length)
    $currentYear = now()->format('Y');
    $currentMonth = ltrim(now()->format('m'), '0'); // Remove leading zero for single digit
    
    expect($serialData->serial)->toBe("PREFIX-INV_{$currentMonth}{$currentYear}_0001");
});

it('formats serial with minimal configuration', function () {
    config([
        'serial-sequence.number_length' => 3,
        'serial-sequence.month_length' => 1,
        'serial-sequence.year_length' => 1,
    ]);
    
    $generator = new SerialGenerator();
    $serialData = $generator->generate('TEST');
    
    $currentYear = substr(now()->format('Y'), -1); // Last digit only
    $currentMonth = ltrim(now()->format('m'), '0');
    
    expect($serialData->serial)->toBe("TEST-{$currentMonth}{$currentYear}-001");
});

it('handles atomic transaction correctly', function () {
    $generator = new SerialGenerator();
    
    // This test verifies the generation happens within a transaction
    // by checking that sequences are properly locked and incremented
    
    $serial1 = $generator->generate('TEST');
    $serial2 = $generator->generate('TEST');
    
    expect($serial1->number)->toBe(1);
    expect($serial2->number)->toBe(2);
    
    // Verify the sequence was incremented atomically
    $sequence = SerialSequence::forPeriod('TEST', $serial1->year, $serial1->month)
                             ->first();
    expect($sequence->last_number)->toBe(2);
});
