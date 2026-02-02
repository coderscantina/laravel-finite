# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-02-02

### Changed

- **BREAKING**: Renamed package from `neon/finite` to `coderscantina/laravel-finite`
- **BREAKING**: Changed namespace from `Neon\Finite\` to `CodersCantina\LaravelFinite\`
- **BREAKING**: Updated minimum PHP version to 8.1+ (from 7.2+)
- **BREAKING**: Updated Laravel support to 10.0+, 11.0+, 12.0+ only (dropped support for 5.4-9.x)
- Replaced all DocBlock type annotations with native PHP type hints
    - All parameters now have explicit type declarations
    - All return types are now declared
    - Union types used where appropriate (e.g., `Transition|string`, `?string`)
    - Mixed and array|ArrayAccess types properly declared
- Updated all property declarations with explicit types
- Updated minimum testbench version to 7.0+ (from 5.5)
- Updated PHPUnit to 10.0+ and 11.0+ support (from 7.0-9.0)
- Updated PHP_CodeSniffer to 3.9+ (from 3.5)

### Added

- MIT License (added LICENSE file)
- Enhanced README with comprehensive documentation
    - Quick start guide
    - Feature descriptions with emojis
    - Advanced usage examples
    - API reference
    - Contributing guidelines
- Improved PHPUnit configuration for PHPUnit 11+ compatibility
- Added explicit return type hints to all methods
- Added property type declarations to all classes

### Fixed

- Fixed `AbstractTestCase::getServiceProviderClass()` to properly override parent static method
- Fixed `Transition` constructor nullable parameter hint (`?string $to` instead of `string $to = null`)
- Fixed `StateMachine::getInitialState()` to return proper nullable type and use modern collection methods
- Fixed `StateTrait::getState()` return type to be nullable `?string`
- Updated phpunit.xml to remove deprecated attributes for PHPUnit 11 compatibility

### Security

- Updated security contact email to m.wallner@coderscantina.com

### Migration Guide

#### Package Name Change

```bash
# Old
composer require neon/finite

# New
composer require coderscantina/laravel-finite
```

#### Namespace Change

```php
// Old
use Neon\Finite\StateTrait;
use Neon\Finite\StateMachine;

// New
use CodersCantina\LaravelFinite\StateTrait;
use CodersCantina\LaravelFinite\StateMachine;
```

#### ServiceProvider Change

```php
// Old
Neon\Finite\ServiceProvider::class,

// New
CodersCantina\LaravelFinite\ServiceProvider::class,
```

#### PHP and Laravel Version Requirements

- Minimum PHP: 8.1 (was 7.2)
- Minimum Laravel: 10.0 (was 5.4)
- Recommended PHP: 8.2 or 8.3+
- Supported Laravel: 10.x, 11.x, 12.x

## [1.0.0] - 2017-06-23

### Added

- Initial release
- Finite state machine implementation for Laravel/Eloquent models
- Support for state management with defined transitions
- Property application during transitions
- Guard clauses for conditional transitions
- Event listeners for pre/post transition hooks
- Fluent API for programmatic configuration
- Support for Laravel 5.4-10.x
- Support for PHP 7.2+
