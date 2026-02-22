<?php

declare(strict_types=1);

use Tests\Models\Invoice;
use Tests\Models\Order;
use Tests\Models\Booking;

beforeEach(function () {
    // Clean up before each test
    \Mawuva\LaravelSerialSequence\Models\SerialSequence::query()->delete();
    Invoice::query()->delete();
    Order::query()->delete();
    Booking::query()->delete();
});

it('generates serial numbers automatically when creating models', function () {
    $invoice = Invoice::create([
        'amount' => 100.00,
        'customer_id' => 1,
        'status' => 'draft',
    ]);
    
    expect($invoice->serial)->not->toBeNull();
    expect($invoice->serie)->toBe('INV');
    expect($invoice->serial_year)->toBe((int) now()->format('Y'));
    expect($invoice->serial_month)->toBe((int) now()->format('m'));
    expect($invoice->serial_number)->toBe(1);
    
    $order = Order::create([
        'total' => 200.00,
        'customer_id' => 2,
        'status' => 'pending',
    ]);
    
    expect($order->serial)->not->toBeNull();
    expect($order->serie)->toBe('ORD');
    expect($order->serial_number)->toBe(1);
    
    $booking = Booking::create([
        'date' => now()->addDays(7),
        'client_id' => 3,
        'status' => 'confirmed',
        'amount' => 150.00,
    ]);
    
    expect($booking->serial)->not->toBeNull();
    expect($booking->serie)->toBe('BKG');
    expect($booking->serial_number)->toBe(1);
});

it('increments serial numbers correctly for each model type', function () {
    // Create multiple invoices
    $invoice1 = Invoice::create(['amount' => 100, 'customer_id' => 1, 'status' => 'draft']);
    $invoice2 = Invoice::create(['amount' => 200, 'customer_id' => 2, 'status' => 'draft']);
    $invoice3 = Invoice::create(['amount' => 300, 'customer_id' => 3, 'status' => 'draft']);
    
    expect($invoice1->serial_number)->toBe(1);
    expect($invoice2->serial_number)->toBe(2);
    expect($invoice3->serial_number)->toBe(3);
    
    // Create multiple orders
    $order1 = Order::create(['total' => 100, 'customer_id' => 1, 'status' => 'pending']);
    $order2 = Order::create(['total' => 200, 'customer_id' => 2, 'status' => 'pending']);
    
    expect($order1->serial_number)->toBe(1);
    expect($order2->serial_number)->toBe(2);
    
    // Verify invoice numbers are not affected by orders
    $invoice4 = Invoice::create(['amount' => 400, 'customer_id' => 4, 'status' => 'draft']);
    expect($invoice4->serial_number)->toBe(4);
});

it('maintains independent sequences for different model types', function () {
    // Create mixed models
    $invoice1 = Invoice::create(['amount' => 100, 'customer_id' => 1, 'status' => 'draft']);
    $order1 = Order::create(['total' => 200, 'customer_id' => 2, 'status' => 'pending']);
    $booking1 = Booking::create(['date' => now()->addDays(7), 'client_id' => 3, 'status' => 'confirmed', 'amount' => 150]);
    
    $invoice2 = Invoice::create(['amount' => 300, 'customer_id' => 3, 'status' => 'draft']);
    $order2 = Order::create(['total' => 400, 'customer_id' => 4, 'status' => 'pending']);
    
    // Each type should have its own sequence
    expect($invoice1->serial_number)->toBe(1);
    expect($invoice2->serial_number)->toBe(2);
    
    expect($order1->serial_number)->toBe(1);
    expect($order2->serial_number)->toBe(2);
    
    expect($booking1->serial_number)->toBe(1);
    
    // Verify different series
    expect($invoice1->serie)->toBe('INV');
    expect($order1->serie)->toBe('ORD');
    expect($booking1->serie)->toBe('BKG');
});

it('allows manual serial assignment', function () {
    $invoice = Invoice::create([
        'serial' => 'MANUAL-123',
        'serie' => 'MANUAL',
        'serial_year' => 2024,
        'serial_month' => 2,
        'serial_number' => 123,
        'amount' => 100.00,
        'customer_id' => 1,
        'status' => 'draft',
    ]);
    
    expect($invoice->serial)->toBe('MANUAL-123');
    expect($invoice->serie)->toBe('MANUAL');
    expect($invoice->serial_year)->toBe(2024);
    expect($invoice->serial_month)->toBe(2);
    expect($invoice->serial_number)->toBe(123);
});

it('works with model factories', function () {
    $invoices = Invoice::factory()->count(5)->create();
    
    expect($invoices)->toHaveCount(5);
    
    foreach ($invoices as $index => $invoice) {
        expect($invoice->serial)->not->toBeNull();
        expect($invoice->serie)->toBe('INV');
        expect($invoice->serial_number)->toBe($index + 1);
    }
});

it('can query models using serial scopes', function () {
    // Create test data
    $invoice1 = Invoice::create(['amount' => 100, 'customer_id' => 1, 'status' => 'draft']);
    $invoice2 = Invoice::create(['amount' => 200, 'customer_id' => 2, 'status' => 'draft']);
    $order1 = Order::create(['total' => 300, 'customer_id' => 3, 'status' => 'pending']);
    
    // Test scopes
    $currentYear = (int) now()->format('Y');
    $currentMonth = (int) now()->format('m');
    
    $invoices = Invoice::serialPeriod('INV', $currentYear, $currentMonth)->get();
    expect($invoices)->toHaveCount(2);
    
    $firstInvoice = Invoice::serialNumber('INV', 1)->first();
    expect($firstInvoice)->not->toBeNull();
    expect($firstInvoice->id)->toBe($invoice1->id);
    
    $invInvoices = Invoice::bySerie('INV')->get();
    expect($invInvoices)->toHaveCount(2);
    
    $ordInvoices = Order::bySerie('ORD')->get();
    expect($ordInvoices)->toHaveCount(1);
});

it('handles database transactions correctly', function () {
    expect(function () {
        // Start a transaction and create an invoice
        \Illuminate\Support\Facades\DB::transaction(function () {
            Invoice::create([
                'amount' => 100.00,
                'customer_id' => 1,
                'status' => 'draft',
            ]);
            
            // Force an error to rollback
            throw new \Exception('Test rollback');
        });
    })->toThrow(\Exception::class);
    
    // Verify no invoice was created and no sequence was incremented
    expect(Invoice::count())->toBe(0);
    
    $sequence = \Mawuva\LaravelSerialSequence\Models\SerialSequence::where('serie', 'INV')->first();
    expect($sequence)->toBeNull();
});
