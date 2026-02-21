<?php

declare(strict_types=1);

namespace Mawuva\LaravelSerialSequence\Contracts;

use Mawuva\LaravelSerialSequence\Data\SerialData;

interface HasSerial
{
    /**
     * Get the business serie identifier for this model.
     * 
     * This method should return the serie code that will be used to generate
     * the serial number (e.g., 'INV' for invoices, 'ORD' for orders, 'BKG' for bookings).
     * 
     * @return string The serie identifier (max 10 characters)
     */
    public function serialSerie(): string;

    /**
     * Set the serial attributes on the model after generation.
     * 
     * This method is called after the serial number is generated and should
     * populate the model's serial-related attributes from the SerialData object.
     * 
     * @param SerialData $data $data
     * @return void
     */
    public function setSerialAttributes(SerialData $data): void;
}
