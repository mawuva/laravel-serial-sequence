<?php

declare(strict_types=1);

namespace Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mawuva\LaravelSerialSequence\Concerns\HasSerialSequence;
use Mawuva\LaravelSerialSequence\Contracts\HasSerial;

class Order extends Model implements HasSerial
{
    use HasFactory, HasSerialSequence;

    protected $fillable = [
        'serial',
        'serie',
        'serial_year',
        'serial_month',
        'serial_number',
        'total',
        'status',
        'customer_id',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'serial_year' => 'integer',
        'serial_month' => 'integer',
        'serial_number' => 'integer',
    ];

    /**
     * Get the business serie identifier for orders.
     */
    public function serialSerie(): string
    {
        return 'ORD';
    }
}
