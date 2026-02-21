<?php

// config for Mawuva/LaravelSerialSequence
return [
    /*
    |--------------------------------------------------------------------------
    | Separator between serial parts
    |--------------------------------------------------------------------------
    | Example: INV-2302-000123
    */
    'separator' => '-',

    /*
    |--------------------------------------------------------------------------
    | Separator between prefix and serial
    |--------------------------------------------------------------------------
    | Example: PREFIX/INV-2302-000123
    */
    'prefix_separator' => '/',

    /*
    |--------------------------------------------------------------------------
    | Number length
    |--------------------------------------------------------------------------
    | The serial number will be left-padded with zeros to reach this length
    */
    'number_length' => 6,

    /*
    |--------------------------------------------------------------------------
    | Month length
    |--------------------------------------------------------------------------
    | How many digits for the month in the serial
    */
    'month_length' => 2,

    /*
    |--------------------------------------------------------------------------
    | Year length
    |--------------------------------------------------------------------------
    | How many digits from the year to use
    */
    'year_length' => 2,

    /*
    |--------------------------------------------------------------------------
    | Optional prefix resolver
    |--------------------------------------------------------------------------
    | You can provide a callable that receives the model and returns a prefix string
    */
    'prefix_resolver' => null,
];
