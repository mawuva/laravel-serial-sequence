<?php

declare(strict_types=1);

use Mawuva\LaravelSerialSequence\Concerns\HasSerialSequence;
use Mawuva\LaravelSerialSequence\Data\SerialData;
use Mawuva\LaravelSerialSequence\Observers\SerialSequenceObserver;
use Tests\Models\Invoice;

beforeEach(function () {
    // Clean up before each test
    \Mawuva\LaravelSerialSequence\Models\SerialSequence::query()->delete();
});

it('registers observer on boot', function () {
    // If the observer is registered, creating an Invoice should generate a serial
    $invoice = Invoice::create(['amount' => 100, 'customer_id' => 1, 'status' => 'draft']);

    expect($invoice->serial)->not->toBeNull();
    expect($invoice->serie)->toBe('INV');
    expect($invoice->serial_year)->toBe((int) now()->format('Y'));
    expect($invoice->serial_month)->toBe((int) now()->format('m'));
    expect($invoice->serial_number)->toBe(1);
});

it('sets serial attributes from SerialData', function () {
    $invoice = new Invoice();
    
    $serialData = new SerialData(
        serial: 'INV-0224-000123',
        serie: 'INV',
        year: 2024,
        month: 2,
        number: 123
    );
    
    $invoice->setSerialAttributes($serialData);
    
    expect($invoice->serial)->toBe('INV-0224-000123');
    expect($invoice->serie)->toBe('INV');
    expect($invoice->serial_year)->toBe(2024);
    expect($invoice->serial_month)->toBe(2);
    expect($invoice->serial_number)->toBe(123);
});

it('uses serialPeriod scope correctly', function () {
    // Create test invoices
    Invoice::create(['amount' => 100, 'customer_id' => 1, 'status' => 'draft']);
    Invoice::create(['amount' => 200, 'customer_id' => 2, 'status' => 'draft']);
    
    // Test scope
    $currentYear = (int) now()->format('Y');
    $currentMonth = (int) now()->format('m');
    
    $invoices = Invoice::serialPeriod('INV', $currentYear, $currentMonth)->get();
    
    expect($invoices)->toHaveCount(2);
    $invoices->each(function ($invoice) use ($currentYear, $currentMonth) {
        expect($invoice->serie)->toBe('INV');
        expect($invoice->serial_year)->toBe($currentYear);
        expect($invoice->serial_month)->toBe($currentMonth);
    });
});

it('uses serialNumber scope correctly', function () {
    // Create test invoices with different serial numbers
    $invoice1 = Invoice::create(['amount' => 100, 'customer_id' => 1, 'status' => 'draft']);
    $invoice2 = Invoice::create(['amount' => 200, 'customer_id' => 2, 'status' => 'draft']);
    
    // Query by serial number
    $invoices = Invoice::serialNumber('INV', 1)->get();
    
    expect($invoices)->toHaveCount(1);
    expect($invoices->first()->serial_number)->toBe(1);
    expect($invoices->first()->serial)->toContain('-000001');
});

it('uses bySerie scope correctly', function () {
    // Create invoices and orders
    Invoice::create(['amount' => 100, 'customer_id' => 1, 'status' => 'draft']);
    Invoice::create(['amount' => 200, 'customer_id' => 2, 'status' => 'draft']);
    
    $invoices = Invoice::bySerie('INV')->get();
    
    expect($invoices)->toHaveCount(2);
    $invoices->each(function ($invoice) {
        expect($invoice->serie)->toBe('INV');
    });
});

it('uses byYear scope correctly', function () {
    Invoice::create(['amount' => 100, 'customer_id' => 1, 'status' => 'draft']);
    
    $currentYear = (int) now()->format('Y');
    $invoices = Invoice::byYear($currentYear)->get();
    
    expect($invoices)->toHaveCount(1);
    expect($invoices->first()->serial_year)->toBe($currentYear);
});

it('uses byMonth scope correctly', function () {
    Invoice::create(['amount' => 100, 'customer_id' => 1, 'status' => 'draft']);
    
    $currentMonth = (int) now()->format('m');
    $invoices = Invoice::byMonth($currentMonth)->get();
    
    expect($invoices)->toHaveCount(1);
    expect($invoices->first()->serial_month)->toBe($currentMonth);
});

it('uses serialNumberFrom scope correctly', function () {
    // Create multiple invoices
    Invoice::create(['amount' => 100, 'customer_id' => 1, 'status' => 'draft']);
    Invoice::create(['amount' => 200, 'customer_id' => 2, 'status' => 'draft']);
    Invoice::create(['amount' => 300, 'customer_id' => 3, 'status' => 'draft']);
    
    // Query from serial number 2
    $invoices = Invoice::serialNumberFrom(2)->get();
    
    expect($invoices)->toHaveCount(2); // Serials 2 and 3
    $invoices->each(function ($invoice) {
        expect($invoice->serial_number)->toBeGreaterThanOrEqual(2);
    });
});

it('uses serialNumberTo scope correctly', function () {
    // Create multiple invoices
    Invoice::create(['amount' => 100, 'customer_id' => 1, 'status' => 'draft']);
    Invoice::create(['amount' => 200, 'customer_id' => 2, 'status' => 'draft']);
    Invoice::create(['amount' => 300, 'customer_id' => 3, 'status' => 'draft']);
    
    // Query to serial number 2
    $invoices = Invoice::serialNumberTo(2)->get();
    
    expect($invoices)->toHaveCount(2); // Serials 1 and 2
    $invoices->each(function ($invoice) {
        expect($invoice->serial_number)->toBeLessThanOrEqual(2);
    });
});

it('can chain scopes together', function () {
    // Create test data
    Invoice::create(['amount' => 100, 'customer_id' => 1, 'status' => 'draft']);
    Invoice::create(['amount' => 200, 'customer_id' => 2, 'status' => 'draft']);
    
    $currentYear = (int) now()->format('Y');
    $currentMonth = (int) now()->format('m');
    
    // Chain multiple scopes
    $invoices = Invoice::bySerie('INV')
                        ->byYear($currentYear)
                        ->byMonth($currentMonth)
                        ->serialNumberFrom(1)
                        ->get();
    
    expect($invoices)->toHaveCount(2);
});
