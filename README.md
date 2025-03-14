# Laravel Filter

A powerful, flexible, and database-agnostic query filtering package for Laravel applications.

## Features

- **Universal Adaptability:** Support for any data source (SQL, NoSQL, API)
- **Intuitive API:** Simple to use, with minimal boilerplate
- **Progressive Enhancement:** Easy for simple cases, powerful for complex ones
- **Multiple Database Support:** Works with MySQL, PostgreSQL, SQLite, and MongoDB
- **Modern API Integration:** First-class support for Laravel API resources
- **SPA-Friendly:** Dynamic front-end filtering capabilities
- **Advanced Filtering:** Complex conditions, nested relationships, and more
- **Performance Optimized:** Efficient query building and caching

## Installation

```bash
composer require rashadkhan/laravel-filter
```

Publish the configuration:

```bash
php artisan vendor:publish --provider="RashadKhan\LaravelFilter\QueryFilterServiceProvider"
```

## Basic Usage

### 1. Add Filterable Trait to Your Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RashadKhan\LaravelFilter\Traits\Filterable;

class Product extends Model
{
    use Filterable;

    // Optionally specify a custom filter service class
    protected $filterServiceClass = \App\Filters\ProductFilter::class;
}
```

### 2. Create a Filter Class

Generate a filter class automatically:

```bash
php artisan make:filter Product
```

Or manually create one:

```php
<?php

namespace App\Filters;

use RashadKhan\LaravelFilter\FilterService;

class ProductFilter extends FilterService
{
    protected $allowedFilters = [
        'id' => ['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'in', 'between'],
        'name' => ['eq', 'neq', 'like'],
        'price' => ['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'between'],
        'category_id' => ['eq', 'neq', 'in'],
        'created_at' => ['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'between'],
    ];

    protected $searchableFields = [
        'name',
        'description',
        'sku'
    ];
}
```

### 3. Use in Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use RashadKhan\LaravelFilter\Traits\HandlesApiRequests;

class ProductController extends Controller
{
    use HandlesApiRequests;

    public function index(Request $request)
    {
        return $this->filteredIndex($request, Product::class, \App\Http\Resources\ProductResource::class);
    }
}
```

### 4. Make API Requests

```http
GET /api/products?search=wireless&filter[price][operator]=gt&filter[price][value]=100&sort=price:desc
```

## Advanced Usage

### Custom Filters

You can customize the filter application by overriding methods in your filter class:

```php
<?php

namespace App\Filters;

use RashadKhan\LaravelFilter\FilterService;
use Illuminate\Database\Eloquent\Builder;

class ProductFilter extends FilterService
{
    public function applyCustomFilters(Builder $query): Builder
    {
        if ($this->hasFilter('custom_condition')) {
            $query->where('custom_field', $this->getFilterValue('custom_condition'));
        }
        return $query;
    }
}
```

---

# Contributing to Laravel Filter

We welcome contributions to Laravel Filter! 🎉

## How to Contribute

1. **Fork the Repository**: Click the "Fork" button at the top-right of the repository.
2. **Clone Your Fork**:
   ```bash
   git clone https://github.com/rashadkhan359/laravel-filter.git
   cd laravel-filter
   ```
3. **Create a New Branch**:
   ```bash
   git checkout -b feature/your-feature-name
   ```
4. **Make Changes & Commit**:
   - Follow the coding standards (PSR-4).
   - Run tests before submitting (`php artisan test`).
   - Write meaningful commit messages.

   ```bash
   git commit -m "Add feature: description"
   ```

5. **Push to Your Fork & Create a Pull Request**:
   ```bash
   git push origin feature/your-feature-name
   ```
   Then, create a Pull Request (PR) from GitHub.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Code of Conduct

By participating in this project, you agree to abide by our [Code of Conduct](CODE_OF_CONDUCT.md).

## Reporting Issues

- If you find a bug, [open an issue](https://github.com/rashadkhan359/laravel-filter/issues) with a detailed description.
- Suggest improvements by opening a feature request issue.

---

# Security Policy

## Reporting a Vulnerability

If you discover a security vulnerability in Laravel Filter, please report it confidentially.

- **Email:** rashadkhan359@gmail.com
- **GitHub Issues:** Do NOT use issues for security reports.

We will acknowledge your report within 48 hours and work on a fix promptly.

---

# Changelog

## [1.1.0] - 2025-03-14
### Added
- Support for complex nested filtering.
- Improved performance for large datasets.

## [1.0.0] - 2025-02-28
### Initial Release
- Core filtering functionality.
- Supports MySQL, PostgreSQL, SQLite, MongoDB.

---

# Funding

If you want to support the project, consider sponsoring us:

```yml
.github/FUNDING.yml
```

```yml
github: rashadkhan359
# patreon: yourpatreonusername
```

---

Enjoy using **Laravel Filter** to simplify and optimize your query filtering needs!
