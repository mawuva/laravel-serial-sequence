<?php

declare(strict_types=1);

namespace Mawuva\LaravelSerialSequence\Concerns;

use Illuminate\Database\Schema\Blueprint;

trait HasSerialColumns
{
    public static function addSerialColumns(Blueprint $table, ?string $indexPrefix = null): void
    {
        $table->string('serie', 10);
        $table->smallInteger('serial_year');
        $table->smallInteger('serial_month');
        $table->unsignedInteger('serial_number');
        $table->string('serial')->unique();

        $periodIndexName = $indexPrefix ? "{$indexPrefix}_serial_period" : 'idx_serial_period';
        $numberIndexName = $indexPrefix ? "{$indexPrefix}_serie_number" : 'idx_serie_number';

        $table->index(['serie', 'serial_year', 'serial_month'], $periodIndexName);
        $table->index(['serie', 'serial_number'], $numberIndexName);
    }
}
