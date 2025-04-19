# Laravel Filter

A powerful, flexible filtering solution for Laravel applications that works with any database type and integrates with all major frontend frameworks.

## Features

- **Universal Database Support**: Works with Eloquent, MongoDB, and other databases
- **Frontend Framework Integration**: React, Vue, Livewire, and Alpine.js components included
- **Advanced Filtering**: Text search, numeric range, date filtering, boolean filters, relationship filters
- **Extensible Architecture**: Easily add custom filter types and database drivers
- **AND/OR Logic Support**: Combine filters with different logic operators
- **Performance Optimized**: Lazy-loaded queries for better performance
- **User-Friendly UI**: Beautiful, responsive UI components with customizable themes

## Installation

```bash
composer require rashadkhan/laravel-filter
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="RashadKhan\LaravelFilter\LaravelFilterProvider" --tag="config"
```

If you want to customize the views:

```bash
php artisan vendor:publish --provider="RashadKhan\LaravelFilter\LaravelFilterProvider" --tag="views"
```

For the JavaScript assets:

```bash
php artisan vendor:publish --provider="RashadKhan\LaravelFilter\LaravelFilterProvider" --tag="assets"
```

## Basic Usage

### Configure Your Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RashadKhan\LaravelFilter\Traits\Filterable;

class User extends Model
{
    use Filterable;

    // Define the filter service class for this model
    public function getFilterServiceClass()
    {
        return \App\Filters\UserFilter::class;
    }
}
```

### Create a Filter Service

```php
<?php

namespace App\Filters;

use RashadKhan\LaravelFilter\FilterService;

class UserFilter extends FilterService
{
    protected $allowedFilters = [
        'name' => [
            'type' => 'text',
            'operators' => ['eq', 'contains', 'starts_with', 'ends_with']
        ],
        'email' => [
            'type' => 'text',
            'operators' => ['eq', 'contains']
        ],
        'age' => [
            'type' => 'number',
            'operators' => ['eq', 'gt', 'lt', 'between']
        ],
        'created_at' => [
            'type' => 'date',
            'operators' => ['eq', 'gt', 'lt', 'between']
        ],
        'active' => [
            'type' => 'boolean',
            'operators' => ['eq']
        ],
    ];

    protected $searchableFields = ['name', 'email'];
}
```

### Use in Controller

```php
public function index(Request $request)
{
    $users = User::filter($request->all());

    return response()->json([
        'data' => $users->items(),
        'meta' => [
            'current_page' => $users->currentPage(),
            'from' => $users->firstItem(),
            'last_page' => $users->lastPage(),
            'per_page' => $users->perPage(),
            'to' => $users->lastItem(),
            'total' => $users->total(),
        ]
    ]);
}
```

### Frontend Integration

#### Using with React

```jsx
import { FilterBuilder } from 'laravel-filter/react';

function UsersTable() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(false);

  const fields = [
    { name: 'name', label: 'Name', type: 'text' },
    { name: 'email', label: 'Email', type: 'text' },
    { name: 'age', label: 'Age', type: 'number' },
    { name: 'created_at', label: 'Created At', type: 'date' },
    { name: 'active', label: 'Active', type: 'boolean' }
  ];

  const handleFilterChange = (filterData) => {
    setLoading(true);

    axios.get('/api/users', { params: filterData })
      .then(response => {
        setUsers(response.data.data);
        setLoading(false);
      });
  };

  return (
    <div>
      <FilterBuilder
        fields={fields}
        onFilterChange={handleFilterChange}
        theme="light"
      />

      {/* Your table component */}
    </div>
  );
}
```

#### Using with Vue

```vue
<template>
  <div>
    <filter-builder
      :fields="fields"
      @filter-change="handleFilterChange"
      theme="light"
    />

    <!-- Your table component -->
  </div>
</template>

<script>
import { FilterBuilder } from 'laravel-filter/vue';

export default {
  components: {
    FilterBuilder
  },

  data() {
    return {
      users: [],
      loading: false,
      fields: [
        { name: 'name', label: 'Name', type: 'text' },
        { name: 'email', label: 'Email', type: 'text' },
        { name: 'age', label: 'Age', type: 'number' },
        { name: 'created_at', label: 'Created At', type: 'date' },
        { name: 'active', label: 'Active', type: 'boolean' }
      ]
    };
  },

  methods: {
    handleFilterChange(filterData) {
      this.loading = true;

      axios.get('/api/users', { params: filterData })
        .then(response => {
          this.users = response.data.data;
          this.loading = false;
        });
    }
  }
};
</script>
```

#### Using with Livewire

```php
use Livewire\Component;

class UsersTable extends Component
{
    public $users = [];

    protected $listeners = ['filterChanged' => 'applyFilter'];

    public function mount()
    {
        $this->users = User::paginate(15);
    }

    public function applyFilter($filterData)
    {
        $this->users = User::filter($filterData)->get();
    }

    public function render()
    {
        $fields = [
            ['name' => 'name', 'label' => 'Name', 'type' => 'text'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'text'],
            ['name' => 'age', 'label' => 'Age', 'type' => 'number'],
            ['name' => 'created_at', 'label' => 'Created At', 'type' => 'date'],
            ['name' => 'active', 'label' => 'Active', 'type' => 'boolean']
        ];

        return view('livewire.users-table', [
            'users' => $this->users,
            'fields' => $fields
        ]);
    }
}
```

```blade
<div>
    @livewire('filter-builder', ['fields' => $fields])

    <!-- Your table component -->
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Age</th>
                <th>Created At</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->age }}</td>
                    <td>{{ $user->created_at->format('Y-m-d') }}</td>
                    <td>{{ $user->active ? 'Active' : 'Inactive' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
```

#### Using with Alpine.js

```blade
@push('scripts')
    @filterScripts
@endpush

<div x-data="filterBuilder(@js($fields), {
    onFilterChange: function(filterData) {
        // Fetch data using AJAX
        fetch('/api/users?' + new URLSearchParams(filterData))
            .then(response => response.json())
            .then(data => {
                // Update your table data
            });
    }
})">
    <!-- The filter UI is included in the Alpine component -->

    <!-- Your table component -->
</div>
```

## Advanced Usage

### Creating Custom Filters

```php
<?php

namespace App\Filters;

use RashadKhan\LaravelFilter\Filters\AbstractFilter;

class CustomJsonFilter extends AbstractFilter
{
    public static function jsonContains(string $field, string $key, $value): array
    {
        return self::conditions($field, 'json_contains', [
            'key' => $key,
            'value' => $value
        ]);
    }

    protected function applyJsonContains($query, string $field, $value, string $whereMethod)
    {
        $key = $value['key'] ?? null;
        $searchValue = $value['value'] ?? null;

        if ($whereMethod === 'orWhere') {
            return $query->orWhereJsonContains($field . '->' . $key, $searchValue);
        }

        return $query->whereJsonContains($field . '->' . $key, $searchValue);
    }
}
```

### Creating Custom Drivers

```php
<?php

namespace App\Drivers;

use RashadKhan\LaravelFilter\Drivers\AbstractDriver;
use RashadKhan\LaravelFilter\Contracts\FilterDriverInterface;

class CustomDriver extends AbstractDriver implements FilterDriverInterface
{
    // Implement the required interface methods
}
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
