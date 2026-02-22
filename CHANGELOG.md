# Changelog

All notable changes to `laravel-serial-sequence` will be documented in this file.

## [1.0.1] - 2025-02-22

### Fixed
- Fixed test failures on GitHub due to case sensitivity in migration path (database vs Database)
- Fixed vendor:publish commands not working due to incorrect migration name in service provider
- Added combined publish command documentation in README

### Upgrade Notes
If you have already published the migrations, you may need to republish them:

```bash
# Remove old migration (if it exists)
rm database/migrations/*create_laravel_serial_sequence_table*.php

# Republish with correct tag
php artisan vendor:publish --tag="laravel-serial-sequence-migrations"
```

## [1.0.0] - 2024-02-22

### Added
- Initial release of Laravel Serial Sequence package
- Automatic serial number generation with transaction safety
- Period-based sequences (year/month combinations) 
- Multiple series support for different document types
- Powerful query scopes for filtering and searching
- Database-level uniqueness guarantees
- Optimized indexes for performance
- Flexible configuration options
- HasSerialSequence trait for easy model integration
- HasSerialColumns trait for database migrations
- HasSerial contract implementation
- Comprehensive documentation and examples
