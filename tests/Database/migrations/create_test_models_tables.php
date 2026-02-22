<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('serial')->unique();
            $table->string('serie', 10);
            $table->integer('serial_year');
            $table->integer('serial_month');
            $table->integer('serial_number');
            $table->decimal('amount', 10, 2);
            $table->integer('customer_id');
            $table->string('status');
            $table->timestamps();

            $table->index(['serie', 'serial_year', 'serial_month']);
            $table->index(['serie', 'serial_number']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('serial')->unique();
            $table->string('serie', 10);
            $table->integer('serial_year');
            $table->integer('serial_month');
            $table->integer('serial_number');
            $table->decimal('total', 10, 2);
            $table->string('status');
            $table->integer('customer_id');
            $table->timestamps();

            $table->index(['serie', 'serial_year', 'serial_month']);
            $table->index(['serie', 'serial_number']);
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('serial')->unique();
            $table->string('serie', 10);
            $table->integer('serial_year');
            $table->integer('serial_month');
            $table->integer('serial_number');
            $table->date('date');
            $table->integer('client_id');
            $table->string('status');
            $table->decimal('amount', 10, 2);
            $table->timestamps();

            $table->index(['serie', 'serial_year', 'serial_month']);
            $table->index(['serie', 'serial_number']);
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
