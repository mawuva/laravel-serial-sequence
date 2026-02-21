<?php

declare(strict_types=1);

namespace Mawuva\LaravelSerialSequence\Data;

final class SerialData
{
    public function __construct(
        public readonly string $serial,
        public readonly string $serie,
        public readonly int $year,
        public readonly int $month,
        public readonly int $number,
    ) {}
}
