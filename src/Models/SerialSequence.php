<?php

declare(strict_types=1);

namespace Mawuva\LaravelSerialSequence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SerialSequence extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'uuid',
        'serie',
        'year',
        'month',
        'last_number',
    ];

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Scope to get sequence for a specific period.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $serie
     * @param int $year
     * @param int $month
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForPeriod($query, string $serie, int $year, int $month)
    {
        return $query->where('serie', $serie)
                    ->where('year', $year)
                    ->where('month', $month);
    }
}
