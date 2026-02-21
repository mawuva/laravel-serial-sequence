<?php

declare(strict_types=1);

namespace Mawuva\LaravelSerialSequence\Concerns;

use Illuminate\Database\Schema\Blueprint;

trait HasSerialColumns
{
    public static function addSerialColumns(Blueprint $table): void
    {
        $table->string('serie', 10);
        $table->smallInteger('serial_year');
        $table->smallInteger('serial_month');
        $table->unsignedInteger('serial_number');
        $table->string('serial')->unique();

        $table->index(['serie', 'serial_year', 'serial_month'], 'idx_serial_period');
        $table->index(['serie', 'serial_number'], 'idx_serie_number');
    }
}
