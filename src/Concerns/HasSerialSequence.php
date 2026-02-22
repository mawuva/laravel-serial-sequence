<?php

declare(strict_types=1);

namespace Mawuva\LaravelSerialSequence\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Mawuva\LaravelSerialSequence\Data\SerialData;
use Mawuva\LaravelSerialSequence\Observers\SerialSequenceObserver;

trait HasSerialSequence
{
    /**
     * Boot the trait and register the observer.
     * 
     * This method is automatically called by Laravel when the model boots.
     * It registers the SerialSequenceObserver to handle automatic serial generation.
     * 
     * @return void
     */
    public static function bootHasSerialSequence(): void
    {
        static::observe(SerialSequenceObserver::class);
    }

    /**
     * Set serial attributes from SerialData object.
     * 
     * @param SerialData $data The serial data containing all components
     * @return void
     */
    public function setSerialAttributes(SerialData $data): void
    {
        $this->serial = $data->serial;
        $this->serie = $data->serie;
        $this->serial_year = $data->year;
        $this->serial_month = $data->month;
        $this->serial_number = $data->number;
    }

    /**
     * Scope to get records for a specific serial period.
     * 
     * @param Builder $query
     * @param string $serie The serie identifier
     * @param int $year The year component
     * @param int $month The month component
     * @return Builder
     */
    public function scopeSerialPeriod(Builder $query, string $serie, int $year, int $month): Builder
    {
        return $query->where('serie', $serie)
                     ->where('serial_year', $year)
                     ->where('serial_month', $month);
    }

    /**
     * Scope to get records with a specific serial number in a serie.
     * 
     * @param Builder $query
     * @param string $serie The serie identifier
     * @param int $number The sequential number
     * @return Builder
     */
    public function scopeSerialNumber(Builder $query, string $serie, int $number): Builder
    {
        return $query->where('serie', $serie)
                     ->where('serial_number', $number);
    }

    /**
     * Scope to get records by serie only.
     * 
     * @param Builder $query
     * @param string $serie The serie identifier
     * @return Builder
     */
    public function scopeBySerie(Builder $query, string $serie): Builder
    {
        return $query->where('serie', $serie);
    }

    /**
     * Scope to get records for a specific year.
     * 
     * @param Builder $query
     * @param int $year The year component
     * @return Builder
     */
    public function scopeByYear(Builder $query, int $year): Builder
    {
        return $query->where('serial_year', $year);
    }

    /**
     * Scope to get records for a specific month.
     * 
     * @param Builder $query
     * @param int $month The month component
     * @return Builder
     */
    public function scopeByMonth(Builder $query, int $month): Builder
    {
        return $query->where('serial_month', $month);
    }

    /**
     * Scope to get records with serial number greater than or equal to a value.
     * 
     * @param Builder $query
     * @param int $number The minimum serial number
     * @return Builder
     */
    public function scopeSerialNumberFrom(Builder $query, int $number): Builder
    {
        return $query->where('serial_number', '>=', $number);
    }

    /**
     * Scope to get records with serial number less than or equal to a value.
     * 
     * @param Builder $query
     * @param int $number The maximum serial number
     * @return Builder
     */
    public function scopeSerialNumberTo(Builder $query, int $number): Builder
    {
        return $query->where('serial_number', '<=', $number);
    }
}
