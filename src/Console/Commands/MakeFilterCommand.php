<?php

namespace RashadKhan\LaravelFilter\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class MakeFilterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:filter {model : The model class to create a filter for}
                           {--force : Force creation even if the filter already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new filter class for a model';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $modelName = $this->argument('model');
        $modelClass = $this->qualifyModel($modelName);

        // Check if the model exists
        if (!class_exists($modelClass)) {
            $this->error("Model [{$modelClass}] does not exist.");
            return 1;
        }

        // Create filter class name and path
        $filterClass = Str::studly($modelName) . 'Filter';
        $filterNamespace = config('query-filter.filter_namespace');
        $filterPath = app_path(str_replace('\\', '/', str_replace('App\\', '', $filterNamespace))) . "/{$filterClass}.php";

        // Check if filter already exists
        if (file_exists($filterPath) && !$this->option('force')) {
            $this->error("Filter [{$filterClass}] already exists.");
            return 1;
        }

        // Get model fields
        $model = new $modelClass();
        $table = $model->getTable();
        $columns = Schema::getColumnListing($table);

        // Generate filter content
        $filterContent = $this->generateFilterContent($filterNamespace, $filterClass, $columns, $table);

        // Create directory if it doesn't exist
        $directory = dirname($filterPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Write filter class
        file_put_contents($filterPath, $filterContent);

        $this->info("Filter [{$filterClass}] created successfully!");
        return 0;
    }

    /**
     * Generate the filter class content.
     *
     * @param string $namespace
     * @param string $className
     * @param array $columns
     * @return string
     */
    protected function generateFilterContent(string $namespace, string $className, array $columns, string $table): string
    {
        $allowedFilters = [];

        foreach ($columns as $column) {
            // Skip Laravel's timestamp columns for search
            if (!in_array($column, ['created_at', 'updated_at', 'deleted_at'])) {
                $searchableFields[] = "'{$column}'";
            }

            // Set allowed operators based on column type
            $columnType = Schema::getColumnType($table, $column);

            switch ($columnType) {
                case 'integer':
                case 'bigint':
                case 'float':
                case 'double':
                case 'decimal':
                    $operators = "['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'in', 'between']";
                    break;
                case 'string':
                case 'text':
                    $operators = "['eq', 'neq', 'like']";
                    break;
                case 'datetime':
                case 'date':
                case 'timestamp':
                    $operators = "['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'between']";
                    break;
                case 'boolean':
                    $operators = "['eq']";
                    break;
                default:
                    $operators = "['eq', 'neq']";
            }

            $allowedFilters[] = "'{$column}' => {$operators}";
        }

        $allowedFiltersStr = implode(",\n", $allowedFilters);
        $searchableFieldsStr = implode(",\n", $searchableFields ?? []);

        return <<<PHP
<?php

namespace {$namespace};

use YourVendor\LaravelQueryFilter\FilterService;
use Illuminate\Database\Eloquent\Builder;

class {$className} extends FilterService
{
    /**
     * The allowed filters with their operators.
     *
     * @var array
     */
    protected \$allowedFilters = [
{$allowedFiltersStr}
    ];

    /**
     * The searchable fields.
     *
     * @var array
     */
    protected \$searchableFields = [
{$searchableFieldsStr}
    ];
}
PHP;
    }

    /**
     * Qualify the given model class base name.
     *
     * @param string $model
     * @return string
     */
    protected function qualifyModel(string $model): string
    {
        $model = ltrim($model, '\\/');
        $model = str_replace('/', '\\', $model);

        if (Str::startsWith($model, 'App\\')) {
            return $model;
        }

        return 'App\\Models\\' . $model;
    }
}
