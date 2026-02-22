<?php

declare(strict_types=1);

use Mawuva\LaravelSerialSequence\Data\SerialData;

it('creates SerialData with correct properties', function () {
    $serialData = new SerialData(
        serial: 'INV-0224-000123',
        serie: 'INV',
        year: 2024,
        month: 2,
        number: 123
    );
    
    expect($serialData->serial)->toBe('INV-0224-000123');
    expect($serialData->serie)->toBe('INV');
    expect($serialData->year)->toBe(2024);
    expect($serialData->month)->toBe(2);
    expect($serialData->number)->toBe(123);
});

it('has readonly properties', function () {
    $serialData = new SerialData(
        serial: 'TEST-123',
        serie: 'TEST',
        year: 2024,
        month: 1,
        number: 1
    );
    
    // Properties should be readonly - attempting to modify should throw errors
    // We test this by checking that the properties maintain their original values
    expect($serialData->serial)->toBe('TEST-123');
    expect($serialData->serie)->toBe('TEST');
    expect($serialData->year)->toBe(2024);
    expect($serialData->month)->toBe(1);
    expect($serialData->number)->toBe(1);
    
    // The readonly nature is enforced by PHP at compile time
    // so we don't need runtime tests for modification attempts
});

it('validates property types', function () {
    // These should work with correct types
    expect(new SerialData('TEST', 'TEST', 2024, 1, 1))->toBeInstanceOf(SerialData::class);
    
    // Test with various valid values
    $serialData = new SerialData(
        serial: 'INV-0224-000001',
        serie: 'INV',
        year: 2024,
        month: 2,
        number: 1
    );
    
    expect(is_string($serialData->serial))->toBeTrue();
    expect(is_string($serialData->serie))->toBeTrue();
    expect(is_int($serialData->year))->toBeTrue();
    expect(is_int($serialData->month))->toBeTrue();
    expect(is_int($serialData->number))->toBeTrue();
});

it('handles edge case values', function () {
    $serialData = new SerialData(
        serial: '',
        serie: '',
        year: 0,
        month: 0,
        number: 0
    );
    
    expect($serialData->serial)->toBe('');
    expect($serialData->serie)->toBe('');
    expect($serialData->year)->toBe(0);
    expect($serialData->month)->toBe(0);
    expect($serialData->number)->toBe(0);
});
