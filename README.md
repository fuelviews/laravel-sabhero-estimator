# Laravel Sab Hero Estimator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/fuelviews/laravel-sabhero-estimator.svg?style=flat-square)](https://packagist.org/packages/fuelviews/laravel-sabhero-estimator)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/fuelviews/laravel-sabhero-estimator/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/fuelviews/laravel-sabhero-estimator/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/fuelviews/laravel-sabhero-estimator/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/fuelviews/laravel-sabhero-estimator/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/fuelviews/laravel-sabhero-estimator.svg?style=flat-square)](https://packagist.org/packages/fuelviews/laravel-sabhero-estimator)

A comprehensive painting project estimator package for Laravel applications. This package provides a complete solution for collecting project details, calculating estimates, and managing painting project quotes with support for both interior and exterior projects.

## Features

- 🎨 **Multi-step Livewire Component** - Interactive estimation wizard
- 📊 **Intelligent Calculations** - Separate logic for interior/exterior projects
- 🛠️ **FilamentPHP Admin Panel** - Complete management interface
- 📱 **Responsive Design** - Mobile-friendly estimation forms
- 🔧 **Configurable** - Customizable rates, multipliers, and settings
- 📤 **Form Submission** - External API integration for lead management
- 🗃️ **Database Storage** - Persistent project and configuration data

## Installation

### Quick Installation (Recommended)

```bash
composer require fuelviews/laravel-sabhero-estimator
php artisan sab-hero-estimator:install
```

The install command provides several options:

```bash
# Clean installation (removes old migrations before publishing new ones)
php artisan sab-hero-estimator:install --fresh --force

# Install with automatic seeding
php artisan sab-hero-estimator:install --seed

# Force overwrite existing files
php artisan sab-hero-estimator:install --force

# Silent installation for automation
php artisan sab-hero-estimator:install --fresh --force --no-interaction
```

### Manual Installation

If you prefer manual control:

```bash
# Publish config
php artisan vendor:publish --tag="sabhero-estimator-config"

# Publish migrations (with --force to overwrite existing)
php artisan vendor:publish --tag="sabhero-estimator-migrations" --force

# Run migrations
php artisan migrate

# Seed default data
php artisan sab-hero-estimator:seed

# Publish views (optional, for customization)
php artisan vendor:publish --tag="sabhero-estimator-views"
```

## Configuration

The package publishes a configuration file at `config/sabhero-estimator.php`:

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Table Configuration
    |--------------------------------------------------------------------------
    */
    'table' => [
        'prefix' => 'estimator_', // Customize table name prefix
    ],

    /*
    |--------------------------------------------------------------------------
    | Form Submission Endpoints
    |--------------------------------------------------------------------------
    */
    'form_endpoints' => [
        'production_url' => 'https://api.fuelforms.com/estimator',
        'development_url' => 'https://dev-api.fuelforms.com/estimator',
    ],

    /*
    |--------------------------------------------------------------------------
    | Calculation Defaults
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'currency_symbol' => '$',
        'decimal_places' => 2,
    ],
];
```

### Cross-Config References

You can reference other config files within the estimator config:

```php
'form_endpoints' => [
    'production_url' => config('forms.forms.free_estimate.production_url'),
    'development_url' => config('forms.forms.free_estimate.development_url'),
],
```

## Usage

### Basic Implementation

Add the Livewire component to any Blade template:

```blade
@livewire('estimator::project-estimator')
```

The component provides a complete multi-step estimation process:
1. **Welcome** - Introduction and project type selection
2. **Contact Info** - Customer details and project type
3. **Measurements** - Project-specific measurements and options
4. **Review** - Final estimate display and submission

### API Access

The package provides a consolidated API through the main class:

```php
use Fuelviews\SabHeroEstimator\SabHeroEstimator;

$estimator = app(SabHeroEstimator::class);

// Get available surface types for a project
$surfaces = $estimator->getSurfaceTypes('interior', 'partial');

// Get house styles with images
$houseStyles = $estimator->getHouseStyles();

// Get multipliers by category
$floorOptions = $estimator->getFloorOptions();
$paintConditions = $estimator->getPaintConditionOptions();
$coverageOptions = $estimator->getCoverageOptions();

// Manage settings
$deviationPercentage = $estimator->getDeviationPercentage();
$estimator->setSetting('key', 'value');
```

### Data Access

```php
use Fuelviews\SabHeroEstimator\Models\Project;
use Fuelviews\SabHeroEstimator\Models\Rate;
use Fuelviews\SabHeroEstimator\Models\Multiplier;

// Get all projects with relationships
$projects = Project::with(['areas.surfaces'])->latest()->get();

// Get projects by type
$interiorProjects = Project::where('project_type', 'interior')->get();

// Get high-value projects
$premiumProjects = Project::where('estimated_high', '>', 5000)->get();

// Access rates and multipliers
$rates = Rate::where('project_type', 'interior')->get();
$houseStyles = Multiplier::houseStyle()->get();
```

## FilamentPHP Admin Panel

The package includes comprehensive admin resources:

### Resources Included
- **Projects** - View and manage submitted estimates
- **Rates** - Configure pricing for different surface types
- **Multipliers** - Manage house styles, floors, conditions, coverage, and extras
- **Settings** - Customize deviation percentage and other defaults

### Setup
Register the plugin in your FilamentPHP panel provider:

```php
use Fuelviews\SabHeroEstimator\Filament\EstimatorPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... other configuration
        ->plugins([
            EstimatorPlugin::make(),
        ]);
}
```

## Calculation Logic

### Interior Projects

**Full Interior Calculation:**
```
Base Cost = Total Square Footage × Interior Rate
Extras = Sum of selected interior extras (as multipliers)
Final Cost = Base Cost × (1 + Extras)
```

**Partial Interior Calculation:**
```
Cost = Sum of (Surface Area × Surface Rate) for each surface
```

### Exterior Projects

**Exterior Calculation:**
```
Base Cost = Total Square Footage × Exterior Rate
House Style Multiplier = Applied additively
Floor Multiplier = Applied additively
Condition Multiplier = Applied additively
Coverage Multiplier = Applied multiplicatively (e.g., 75% coverage = ×0.75)

Final Cost = ((Base Cost + Additive Multipliers) × Coverage Multiplier)
```

### Estimate Ranges

All estimates include a configurable deviation percentage for low/high ranges:
```
Low Estimate = Final Cost × (1 - Deviation %)
High Estimate = Final Cost × (1 + Deviation %)
```

## Troubleshooting

### Migration Issues

If you encounter table creation issues:

```bash
# Clean install with fresh migrations
php artisan sab-hero-estimator:install --fresh --force

# Check migration status
php artisan migrate:status | grep estimator
```

### Config Path Issues

If you see errors about missing config paths, ensure you're using the correct structure:

- ✅ **Correct**: `config('sabhero-estimator.table.prefix')`
- ❌ **Incorrect**: `config('sabhero-estimator.database.table_prefix')`

### Table Not Found Errors

This usually indicates migrations weren't run properly:

1. Check if tables exist: `php artisan tinker --execute="DB::table('estimator_multipliers')->count()"`
2. If not found, run: `php artisan sab-hero-estimator:install --fresh --force`

### Form Submission Issues

Verify your form endpoints configuration and ensure external APIs are accessible.

## Package Structure

```
src/
├── Commands/           # Installation and seeding commands
├── Contracts/          # Interface definitions
├── Filament/          # Admin panel resources
├── Http/Livewire/     # Livewire components
├── Models/            # Eloquent models
├── Services/          # Business logic services
└── SabHeroEstimator.php # Main package class
```

## Database Schema

The package creates these tables (with configurable prefix):

- `estimator_projects` - Main project records
- `estimator_areas` - Project areas (for partial interior)
- `estimator_surfaces` - Surface details within areas
- `estimator_rates` - Pricing rates for surface types
- `estimator_multipliers` - Calculation multipliers
- `estimator_settings` - Package configuration

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Sweatybreeze](https://github.com/sweatybreeze)
- [Thejmitchener](https://github.com/thejmitchener)
- [Fuelviews](https://fuelviews.com)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
