# Laravel Sab Hero Estimator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/fuelviews/laravel-sabhero-estimator.svg?style=flat-square)](https://packagist.org/packages/fuelviews/laravel-sabhero-estimator)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/fuelviews/laravel-sabhero-estimator/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/fuelviews/laravel-sabhero-estimator/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/fuelviews/laravel-sabhero-estimator/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/fuelviews/laravel-sabhero-estimator/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/fuelviews/laravel-sabhero-estimator.svg?style=flat-square)](https://packagist.org/packages/fuelviews/laravel-sabhero-estimator)

A comprehensive painting project estimator package for Laravel applications. This package provides a complete solution for collecting project details, calculating estimates, and managing painting project quotes with support for both interior and exterior projects.

## Installation

Install the package via Composer:

```bash
composer require fuelviews/laravel-sabhero-estimator
```

Run the installation command (recommended):

```bash
php artisan sab-hero-estimator:install
```

This will:
- Publish the configuration file
- Publish and run migrations
- Seed default data (rates, multipliers, settings)
- Optionally publish views for customization

## Manual Installation

If you prefer manual installation:

```bash
# Publish config
php artisan vendor:publish --tag="sabhero-estimator-config"

# Publish migrations
php artisan vendor:publish --tag="sabhero-estimator-migrations"

# Run migrations
php artisan migrate

# Seed default data
php artisan sab-hero-estimator:seed

# Publish views (optional)
php artisan vendor:publish --tag="sabhero-estimator-views"
```

## Configuration

Add these environment variables to your `.env` file:

```
```

## Usage

### Basic Usage

Add the Livewire component to any Blade template:

```blade
@livewire('estimator::project-estimator')
```

### User Model Integration

Add the trait to your User model to associate projects:

```php
use Fuelviews\SabHeroEstimator\Traits\HasEstimatorProjects;

class User extends Authenticatable
{
    use HasEstimatorProjects;
}

// Now you can access user projects
$user->estimatorProjects;
$user->interiorProjects;
$user->exteriorProjects;
$user->recentEstimatorProjects(10);
```

### Custom Calculations

Implement your own calculation logic:

```php
use Fuelviews\SabHeroEstimator\Contracts\EstimatorCalculator;

class CustomCalculator implements EstimatorCalculator
{
    public function calculate(array $data): array
    {
        // Your custom calculation logic
        return [
            'low' => 1000.00,
            'high' => 1500.00
        ];
    }
}

// In your service provider
$this->app->bind(EstimatorCalculator::class, CustomCalculator::class);
```

### Accessing Data

```php
use Fuelviews\SabHeroEstimator\Models\Project;

// Get all projects
$projects = Project::with(['areas.surfaces'])->latest()->get();

// Get interior projects only
$interiorProjects = Project::interior()->get();

// Get projects with specific estimate range
$highValueProjects = Project::where('estimated_high', '>', 5000)->get();
```

## Admin Panel (Filament)

The package includes a complete Filament admin panel for managing:

- **Projects**: View submitted estimates and customer details
- **Rates**: Manage pricing for different surface types
- **Multipliers**: Configure house styles, floors, conditions, coverage, and extras
- **Settings**: Customize form labels and default values

Access via your Filament admin panel under the "Estimator" navigation group.

## Package Structure

The package includes:

- **Models**: Project, Area, Surface, Rate, Multiplier, Setting
- **Livewire Component**: Multi-step estimation wizard
- **Filament Resources**: Complete admin interface
- **Services**: Calculation and form submission logic
- **Commands**: Installation and seeding utilities
- **Migrations**: Database schema with configurable table prefixes

## Calculation Logic

### Interior Projects

- **Full Interior**: Base rate × total square footage × (1 + sum of selected extras)
- **Partial Interior**: Sum of (surface area/quantity × surface rate) for each surface

### Exterior Projects

- **Base Cost**: Total square footage × exterior rate
- **Multipliers**: Applied additively for house style, floors, and condition
- **Coverage**: Applied multiplicatively (e.g., 75% = 0.75 multiplier)

Final estimate includes configurable deviation percentage for low/high range.

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
- [Thejmitchener](https://github.com/fuelviews)
- [Fuelviews](htt[s://fuelviews.com])
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
