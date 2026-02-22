# Laravel Serial Sequence

A flexible, transactional, and extensible serial number generator for Laravel.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mawuva/laravel-serial-sequence.svg?style=flat-square)](https://packagist.org/packages/mawuva/laravel-serial-sequence)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mawuva/laravel-serial-sequence/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mawuva/laravel-serial-sequence/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/mawuva/laravel-serial-sequence/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/mawuva/laravel-serial-sequence/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mawuva/laravel-serial-sequence.svg?style=flat-square)](https://packagist.org/packages/mawuva/laravel-serial-sequence)

Laravel Serial Sequence provides a robust solution for generating unique serial numbers with automatic incrementation based on series, year, and month periods. Perfect for invoices, orders, tickets, and any business documents requiring sequential numbering with period-based resets.

## Features

- **Automatic serial generation** with transaction safety
- **Period-based sequences** (year/month combinations)
- **Multiple series support** for different document types
- **Powerful query scopes** for filtering and searching
- **Database-level uniqueness** guarantees
- **Optimized indexes** for performance
- **Flexible configuration** options

## Installation

You can install the package via composer:

```bash
composer require mawuva/laravel-serial-sequence
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="serial-sequence-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="serial-sequence-config"
```

You can publish both migrations and config with:

```bash
php artisan vendor:publish --tag="serial-sequence"
```

## Database Setup

### 1️. Option "Snippet / Migration Example"

Copy-paste this ready-to-use migration snippet into your migration file:

```php
Schema::table('orders', function (Blueprint $table) {
    $table->string('serie', 10);
    $table->smallInteger('serial_year');
    $table->smallInteger('serial_month');
    $table->unsignedInteger('serial_number');
    $table->string('serial')->unique();

    // Index for business search
    $table->index(['serie', 'serial_year', 'serial_month'], 'idx_orders_serial_period');

    // Index for audit / precise search
    $table->index(['serie', 'serial_number'], 'idx_orders_serie_number');
});
```

**Advantages:**
- No magic, fully transparent
- Easy for users who just want to copy-paste
- Complete control over column names and indexes

### 2️. Option "Trait Auto Add Columns" (Recommended)

Use the built-in `HasSerialColumns` trait for cleaner migrations:

```php
use Mawuva\LaravelSerialSequence\Concerns\HasSerialColumns;

Schema::table('orders', function (Blueprint $table) {
    HasSerialColumns::addSerialColumns($table, 'orders');
});
```

**Advantages:**
- ✅ Centralized definition in the package
- ✅ Easy updates and maintenance
- ✅ Consistent across all your models
- ✅ Optional index prefix for better organization

The `addSerialColumns` method automatically creates:
- `serie` (varchar(10)) - Series identifier
- `serial_year` (smallint) - Year component
- `serial_month` (smallint) - Month component  
- `serial_number` (unsigned int) - Sequential number
- `serial` (varchar, unique) - Full serial string
- Optimized indexes for performance

## Usage

### Basic Model Setup

Add the `HasSerialSequence` trait and implement the `HasSerial` contract:

```php
use Illuminate\Database\Eloquent\Model;
use Mawuva\LaravelSerialSequence\Concerns\HasSerialSequence;
use Mawuva\LaravelSerialSequence\Contracts\HasSerial;

class Order extends Model implements HasSerial
{
    use HasSerialSequence;

    protected $fillable = [
        'customer_id',
        'amount',
        // serial fields are managed automatically
        'serie',
        'serial_year', 
        'serial_month',
        'serial_number',
        'serial',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'serial_year' => 'integer',
        'serial_month' => 'integer',
        'serial_number' => 'integer',
    ];

    /**
     * Get the business serie identifier for orders.
     */
    public function serialSerie(): string
    {
        return 'ORD';
    }
}
```

#### Required Methods

When implementing the `HasSerial` contract, you must define:

**`serialSerie(): string`**
- Returns the series identifier for this model type
- Maximum 10 characters
- Examples: 'ORD' for orders, 'INV' for invoices, 'TICKET' for tickets

**`setSerialAttributes(SerialData $data): void`**
- Automatically handled by the `HasSerialSequence` trait
- Populates the model's serial fields after generation
- No manual implementation needed when using the trait

#### Multiple Model Examples

```php
// Invoice model
class Invoice extends Model implements HasSerial
{
    use HasSerialSequence;
    
    public function serialSerie(): string
    {
        return 'INV';
    }
}

// Booking model  
class Booking extends Model implements HasSerial
{
    use HasSerialSequence;
    
    public function serialSerie(): string
    {
        return 'BKG';
    }
}

// Ticket model
class Ticket extends Model implements HasSerial
{
    use HasSerialSequence;
    
    public function serialSerie(): string
    {
        return 'TICKET';
    }
}
```

### Creating Records with Serial Numbers

```php
// Automatic serial generation
$order = Order::create([
    'customer_id' => 1,
    'amount' => 99.99,
]);

// The serial fields are automatically populated:
// - serie: 'ORD' (default or configured)
// - serial_year: 2024
// - serial_month: 2
// - serial_number: 1 (incremented)
// - serial: 'ORD-2024-02-0001'
```

### Query Scopes

The package provides powerful query scopes for filtering:

```php
// Get orders for a specific period
$orders = Order::serialPeriod('ORD', 2024, 2)->get();

// Get orders with specific serial number in a series
$orders = Order::serialNumber('ORD', 5)->get();

// Filter by series only
$orders = Order::bySerie('ORD')->get();

// Filter by year
$orders = Order::byYear(2024)->get();

// Filter by month
$orders = Order::byMonth(2)->get();

// Range queries on serial numbers
$orders = Order::serialNumberFrom(10)->get();      // >= 10
$orders = Order::serialNumberTo(100)->get();       // <= 100
```

### Advanced Examples

```php
// Complex queries combining scopes
$recentOrders = Order::bySerie('INV')
    ->byYear(2024)
    ->serialNumberFrom(50)
    ->orderBy('serial_number', 'desc')
    ->limit(10)
    ->get();

// Get the latest serial for a period
$latestOrder = Order::serialPeriod('ORD', 2024, 2)
    ->orderBy('serial_number', 'desc')
    ->first();
```

## How It Works

### Serial Number Format

The package generates serial numbers in the format: `{SERIE}-{YEAR}-{MONTH}-{NUMBER}`

Example: `ORD-2024-02-0001`

- **SERIE**: Series identifier (e.g., 'ORD' for orders, 'INV' for invoices)
- **YEAR**: 4-digit year (2024)
- **MONTH**: 2-digit month (02)
- **NUMBER**: Zero-padded sequential number (0001)

### Automatic Reset

Serial numbers automatically reset to 1 when:
- The series changes
- The year changes 
- The month changes

This ensures clean separation between different periods and document types.

### Database Structure

The package uses two main tables:

1. **`serial_sequences`** - Tracks the last number used for each series/period combination
2. **Your model tables** - Store the actual serial data

### Transaction Safety

All serial number generation happens within database transactions to prevent:
- Duplicate serial numbers
- Gaps in sequences
- Race conditions in concurrent requests

## Configuration

### Publishing the Config File

Publish the configuration file to customize the serial number format:

```bash
php artisan vendor:publish --tag="laravel-serial-sequence-config"
```

This will create `config/serial-sequence.php` with the default settings.

### Configuration Options

```php
<?php

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
```

### Understanding the Format

With the default configuration, serial numbers are generated as:

```
{SERIE}{separator}{YEAR}{separator}{MONTH}{separator}{NUMBER}
```

**Example with defaults:**
- Serie: 'ORD'
- Year: '24' (2 digits from 2024)
- Month: '02' 
- Number: '000123' (6 digits, zero-padded)
- Result: `ORD-24-02-000123`

### Custom Format Examples

#### Compact Format
```php
'separator' => '',
'year_length' => 4,
'month_length' => 2,
'number_length' => 4,
```
Result: `ORD2024020001`

#### Slash-Separated Format
```php
'separator' => '/',
'year_length' => 4,
'month_length' => 2,
'number_length' => 5,
```
Result: `ORD/2024/02/00001`

#### With Prefix
```php
'prefix_separator' => '|',
'prefix_resolver' => fn($model) => 'COMPANY',
```
Result: `COMPANY|ORD-24-02-000123`

### Advanced Configuration

#### Dynamic Prefix Resolver

You can set a custom prefix resolver that receives the model instance:

```php
'prefix_resolver' => function ($model) {
    if ($model instanceof Order) {
        return $model->company->code;
    }
    
    return null; // No prefix for other models
},
```

#### Environment-Based Configuration

Different formats for different environments:

```php
// config/serial-sequence.php
return [
    'separator' => env('SERIAL_SEPARATOR', '-'),
    'number_length' => env('SERIAL_NUMBER_LENGTH', 6),
    'year_length' => env('SERIAL_YEAR_LENGTH', 2),
    'month_length' => env('SERIAL_MONTH_LENGTH', 2),
];
```

```env
# .env.production
SERIAL_SEPARATOR=-
SERIAL_NUMBER_LENGTH=6

# .env.testing  
SERIAL_SEPARATOR=_
SERIAL_NUMBER_LENGTH=4
```

## Testing

```bash
composer test
```

## FAQ

### Q: Can I use custom serial formats?
A: Yes! Configure the `serial_format` in the config file to match your needs.

### Q: How do I handle multiple document types?
A: Use different series identifiers for each document type (e.g., 'ORD', 'INV', 'TICKET').

### Q: Are serial numbers guaranteed to be unique?
A: Yes, the package uses database constraints and transactions to ensure uniqueness.

### Q: Can I manually set serial numbers?
A: While possible, it's recommended to let the package handle generation automatically.

### Q: What happens if I delete records?
A: Serial numbers are not reused. The sequence continues from the last used number.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/mawuva/laravel-serial-sequence/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Ephraim Seddor](https://github.com/mawuva)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
