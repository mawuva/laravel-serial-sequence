<?php

declare(strict_types=1);

namespace Mawuva\LaravelSerialSequence\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Mawuva\LaravelSerialSequence\Data\SerialData;
use Mawuva\LaravelSerialSequence\Models\SerialSequence;

class SerialGenerator
{
    /**
     * Generate a new serial number for the given serie.
     * 
     * This method handles the complete serial number generation process:
     * - Finds or creates a sequence for the current period
     * - Increments the counter atomically within a transaction
     * - Formats the serial according to configuration
     * 
     * @param string $serie The business serie identifier (e.g., 'INV', 'ORD')
     * @param string|null $prefix Optional prefix to prepend to the serial
     * @param \DateTimeInterface|null $now Date for period calculation (defaults to now)
     * @return SerialData Object containing all serial components
     */
    public function generate(string $serie, ?string $prefix = null, ?\DateTimeInterface $now = null): SerialData
    {
        $now = $now ?? CarbonImmutable::now();
        $year = (int) $now->format('Y');
        $month = (int) $now->format('m');

        return DB::transaction(function () use ($serie, $year, $month, $prefix) {
            [$sequence, $number] = $this->getOrCreateSequenceForPeriod($serie, $year, $month);

            $serial = $this->formatSerial($serie, $year, $month, $number, $prefix);

            return new SerialData(
                serial: $serial,
                serie: $serie,
                year: $year,
                month: $month,
                number: $number
            );
        });
    }

    /**
     * Get or create sequence for the period with automatic reset.
     *
     * This method handles the sequence logic:
     * - Finds existing sequence for the period
     * - Creates new sequence if period changed (automatic reset)
     * - Increments counter for existing sequences
     *
     * @param string $serie The serie identifier
     * @param int $year The year component
     * @param int $month The month component
     * @return array{SerialSequence, int} Sequence and number to use
     */
    protected function getOrCreateSequenceForPeriod(string $serie, int $year, int $month): array
    {
        // Try to find existing sequence for this specific period
        $sequence = SerialSequence::forPeriod($serie, $year, $month)
                            ->lockForUpdate()
                            ->first();

        if (!$sequence) {
            // Create new sequence for this period (automatic reset)
            $sequence = SerialSequence::create([
                'serie' => $serie,
                'year' => $year,
                'month' => $month,
                'last_number' => 1,
            ]);
            $number = 1;
        } else {
            // Increment existing sequence
            $sequence->increment('last_number');
            $number = $sequence->last_number;
        }

        return [$sequence, $number];
    }

    /**
     * Format the serial number according to configuration.
     * 
     * Creates a formatted serial string using the configured separators,
     * padding, and date formats. Example: INV-0224-000123
     * 
     * @param string $serie The serie identifier
     * @param int $year The year component
     * @param int $month The month component
     * @param int $number The sequential number
     * @param string|null $prefix Optional prefix
     * @return string The formatted serial number
     */
    protected function formatSerial(
        string $serie,
        int $year,
        int $month,
        int $number,
        ?string $prefix = null
    ): string {
        $config = config('serial-sequence');

        $separator = $config['separator'] ?? '-';
        $prefixSeparator = $config['prefix_separator'] ?? '/';
        $numberLength = $config['number_length'] ?? 6;
        $monthLength = $config['month_length'] ?? 2;
        $yearLength = $config['year_length'] ?? 2;

        $monthFormatted = str_pad((string)$month, $monthLength, '0', STR_PAD_LEFT);
        $yearFormatted = substr((string)$year, -$yearLength);
        $numberFormatted = str_pad((string)$number, $numberLength, '0', STR_PAD_LEFT);

        $serialParts = [
            $serie,
            $monthFormatted . $yearFormatted,
            $numberFormatted
        ];

        $serial = implode($separator, $serialParts);

        if ($prefix) {
            $serial = $prefix . $prefixSeparator . $serial;
        }

        return $serial;
    }
}
