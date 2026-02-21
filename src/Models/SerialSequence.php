<?php

declare(strict_types=1);

namespace Mawuva\LaravelSerialSequence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SerialSequence extends Model
{
    use HasUuids, SoftDeletes;

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
}
