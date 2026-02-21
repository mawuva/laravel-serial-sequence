<?php

namespace Mawuva\LaravelSerialSequence\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mawuva\LaravelSerialSequence\LaravelSerialSequence
 */
class LaravelSerialSequence extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Mawuva\LaravelSerialSequence\LaravelSerialSequence::class;
    }
}
