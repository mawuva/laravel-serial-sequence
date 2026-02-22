<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mawuva\LaravelSerialSequence\Concerns\HasSerialColumns;

return new class extends Migration
{
    use HasSerialColumns;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            static::addSerialColumns($table, 'invoices');
            $table->decimal('amount', 10, 2);
            $table->integer('customer_id');
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            static::addSerialColumns($table, 'orders');
            $table->decimal('total', 10, 2);
            $table->string('status');
            $table->integer('customer_id');
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            static::addSerialColumns($table, 'bookings');
            $table->date('date');
            $table->integer('client_id');
            $table->string('status');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('bookings');
    }
};
