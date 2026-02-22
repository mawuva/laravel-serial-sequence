<?php

declare(strict_types=1);

use Mawuva\LaravelSerialSequence\Observers\SerialSequenceObserver;
use Tests\Models\Invoice;
use Tests\Models\Order;

beforeEach(function () {
    // Clean up before each test
    \Mawuva\LaravelSerialSequence\Models\SerialSequence::query()->delete();
});

it('automatically generates serial on model creation', function () {
    $observer = new SerialSequenceObserver;

    $invoice = new Invoice([
        'amount' => 100.00,
        'customer_id' => 1,
        'status' => 'draft',
    ]);

    expect($invoice->serial)->toBeNull();

    $observer->creating($invoice);

    expect($invoice->serial)->not->toBeNull();
    expect($invoice->serie)->toBe('INV');
    expect($invoice->serial_year)->toBe((int) now()->format('Y'));
    expect($invoice->serial_month)->toBe((int) now()->format('m'));
    expect($invoice->serial_number)->toBe(1);

    // Check format
    $expectedFormat = 'INV-'.now()->format('my').'-000001';
    expect($invoice->serial)->toBe($expectedFormat);
});

it('skips generation if serial is already set', function () {
    $observer = new SerialSequenceObserver;

    $invoice = new Invoice([
        'serial' => 'MANUAL-123',
        'amount' => 100.00,
        'customer_id' => 1,
        'status' => 'draft',
    ]);

    $originalSerial = $invoice->serial;

    $observer->creating($invoice);

    expect($invoice->serial)->toBe($originalSerial);
    expect($invoice->serial)->toBe('MANUAL-123');
    expect($invoice->serial_number)->toBeNull(); // Not set because we skipped
});

it('calls prefix resolver when configured', function () {
    // Configure a prefix resolver
    config([
        'serial-sequence.prefix_resolver' => function ($model) {
            if ($model instanceof Invoice) {
                return 'INV-'.$model->customer_id;
            }

            return null;
        },
    ]);

    $observer = new SerialSequenceObserver;

    $invoice = new Invoice([
        'amount' => 100.00,
        'customer_id' => 42,
        'status' => 'draft',
    ]);

    $observer->creating($invoice);

    expect($invoice->serial)->toStartWith('INV-42/');
    expect($invoice->serial)->toContain('/INV-');
});

it('works with different model types', function () {
    $observer = new SerialSequenceObserver;

    $invoice = new Invoice(['amount' => 100.00, 'customer_id' => 1, 'status' => 'draft']);
    $order = new Order(['total' => 200.00, 'customer_id' => 2, 'status' => 'pending']);

    $observer->creating($invoice);
    $observer->creating($order);

    expect($invoice->serie)->toBe('INV');
    expect($order->serie)->toBe('ORD');

    expect($invoice->serial_number)->toBe(1);
    expect($order->serial_number)->toBe(1);

    expect($invoice->serial)->toContain('INV-');
    expect($order->serial)->toContain('ORD-');
});

it('handles null prefix resolver gracefully', function () {
    config([
        'serial-sequence.prefix_resolver' => null,
    ]);

    $observer = new SerialSequenceObserver;

    $invoice = new Invoice(['amount' => 100.00, 'customer_id' => 1, 'status' => 'draft']);

    expect(fn () => $observer->creating($invoice))
        ->not->toThrow(\Exception::class);

    expect($invoice->serial)->not->toBeNull();
    expect($invoice->serial)->not->toContain('/');
});

it('increments correctly for multiple models of same type', function () {
    $observer = new SerialSequenceObserver;

    $invoice1 = new Invoice(['amount' => 100.00, 'customer_id' => 1, 'status' => 'draft']);
    $invoice2 = new Invoice(['amount' => 200.00, 'customer_id' => 2, 'status' => 'draft']);

    $observer->creating($invoice1);
    $observer->creating($invoice2);

    expect($invoice1->serial_number)->toBe(1);
    expect($invoice2->serial_number)->toBe(2);

    expect($invoice1->serial)->toContain('-000001');
    expect($invoice2->serial)->toContain('-000002');
});
