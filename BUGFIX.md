# Bug Fix: Infinite Recursion on Model Boot (Laravel 13.5.0+)

## Problem

When using the `HasSerialSequence` trait with Laravel 13.5.0 and PHP 8.4.20, users encountered a `LogicException`:

```
The [Illuminate\Database\Eloquent\Model::bootIfNotBooted] method may not be called on model [App\Models\Order] while it is being booted.
```

This error occurred when creating a new model instance that uses the `HasSerialSequence` trait.

## Root Cause

The issue was in the `bootHasSerialSequence()` method in `src/Concerns/HasSerialSequence.php`:

```php
public static function bootHasSerialSequence(): void
{
    static::observe(SerialSequenceObserver::class);
}
```

The `static::observe()` method internally calls `bootIfNotBooted()` on the model, which creates an infinite recursion loop because:

1. Laravel calls `bootHasSerialSequence()` during the model boot process
2. `static::observe()` tries to boot the model again
3. This triggers `bootIfNotBooted()` while the model is already booting
4. Laravel 13.5.0+ detects this and throws a `LogicException` to prevent infinite recursion

## Solution

Changed the observer registration to directly register the `creating` event instead of using the `observe()` method:

```php
public static function bootHasSerialSequence(): void
{
    static::creating([SerialSequenceObserver::class, 'creating']);
}
```

This approach:
- ✅ Avoids calling `bootIfNotBooted()` during the boot process
- ✅ Still registers the observer correctly
- ✅ Maintains the same functionality
- ✅ Works with Laravel 13.5.0+ and earlier versions

## Testing

A regression test was added to prevent this issue from happening again:

```php
it('does not cause infinite recursion during model boot', function () {
    // This should complete without throwing LogicException
    $invoice = Invoice::create(['amount' => 100, 'customer_id' => 1, 'status' => 'draft']);
    
    expect($invoice)->toBeInstanceOf(Invoice::class);
    expect($invoice->serial)->not->toBeNull();
});
```

## Impact

- **Breaking Change**: No
- **Backward Compatibility**: Yes, fully compatible with older Laravel versions
- **Performance**: No impact
- **Functionality**: No change in behavior

## Files Changed

1. `src/Concerns/HasSerialSequence.php` - Fixed observer registration
2. `tests/Unit/Concerns/HasSerialSequenceTest.php` - Added regression test
3. `CHANGELOG.md` - Documented the fix
