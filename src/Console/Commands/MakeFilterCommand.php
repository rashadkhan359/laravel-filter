<?php

namespace RashadKhan\LaravelFilter\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model;
use RashadKhan\LaravelFilter\FilterManager;
use RashadKhan\LaravelFilter\Contracts\FilterDriverInterface;

class MakeFilterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:filter {model : The model class to create a filter for}
                            {--driver= : The driver to use (eloquent, mongo, etc.)}
                            {--force : Force creation even if the filter already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new filter class for a model';

    /**
     * The filter driver to use.
     *
     * @var FilterDriverInterface
     */
    protected $driver;
    protected $filterManager;

    /**
     * Create a new command instance.
     *
     * @param FilterManager $filterManager
     * @return void
     */
    public function __construct(FilterManager $filterManager)
    {
        parent::__construct();
        $this->filterManager = $filterManager;
    }


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Determine which driver to use (from option or auto-detect)
        $driverName = $this->option('driver');

        if ($driverName) {
            $this->driver = $this->filterManager->driver($driverName);
            $this->info("Using driver: {$driverName}");
        } else {
            $this->driver = $this->filterManager->detectDriver();
            $this->info("Auto-detected driver: " . $this->driver->getName());
        }

        // Check if adapter is available
        if (!$this->driver->getAdapter()) {
            $this->error("No adapter available for driver: " . $this->driver->getName());
            return 1;
        }

        $modelName = $this->argument('model');
        $modelClass = $this->qualifyModel($modelName);

        // Check if the model exists
        if (!class_exists($modelClass)) {
            $this->error("Model [{$modelClass}] does not exist.");
            return 1;
        }

        // Create filter class name and path
        $filterClass = Str::studly($modelName) . 'Filter';
        $filterNamespace = config('laravelfilter.filter_namespace');
        $filterPath = app_path(str_replace('\\', '/', str_replace('App\\', '', $filterNamespace))) . "/{$filterClass}.php";

        // Check if filter already exists
        if (file_exists($filterPath) && !$this->option('force')) {
            $this->error("Filter [{$filterClass}] already exists.");
            return 1;
        }

        // Get model fields
        $model = new $modelClass();
        $table = $model->getTable();
        $connectionType = DB::connection()->getDriverName();

        // Get columns list and their types
        $columns = $this->driver->getAdapter()->getColumns($table);

        if (empty($columns)) {
            $this->warn("No columns found for table: {$table}");
        }

        $columnTypes = $this->getColumnTypes($table, $columns);


        // Generate filter content
        $filterContent = $this->generateFilterContent($filterNamespace, $filterClass, $columns, $columnTypes);

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
     * Get column types for the model.
     *
     * @param string $table
     * @param array $columns
     * @return array
     */
    protected function getColumnTypes(string $table, array $columns): array
    {
        $columnTypes = [];
        $excludeFields = config('laravelfilter.filters.exclude_fields', []);

        foreach ($columns as $column) {
            // Skip excluded fields
            if (in_array($column, $excludeFields)) {
                continue;
            }
            try {
                $columnTypes[$column] = $this->driver->getAdapter()->getColumnType($table, $column);
            } catch (\Exception $e) {
                $this->warn("Could not determine type for column {$column}: " . $e->getMessage());
                $columnTypes[$column] = 'unknown';
            }
        }

        return $columnTypes;
    }

    /**
     * Generate the filter class content.
     *
     * @param string $namespace
     * @param string $className
     * @param array $columns
     * @param array $columnTypes
     * @return string
     */
    protected function generateFilterContent(string $namespace, string $className, array $columns, array $columnTypes): string
    {
        $stubPath = __DIR__ . '/stubs/filter.stub';

        if (!File::exists($stubPath)) {
            $this->error("Stub file not found at {$stubPath}");
            return '';
        }

        $stub = File::get($stubPath);

        $allowedFiltersWithType = [];
        $searchableFields = [];

        foreach ($columns as $column) {
            // Skip Laravel's timestamp columns for search
            if (!in_array($column, ['created_at', 'updated_at', 'deleted_at'])) {
                $searchableFields[] = "'{$column}'";
            }

            // Get column type and map to operators
            $columnType = $columnTypes[$column] ?? 'unknown';

            // Map the column type to operators using the appropriate driver
            $operators = $this->driver->mapColumnTypeToOperators($columnType);
            $operatorsStr = "['" . implode("', '", $operators) . "']";

            $allowedFiltersWithType[] = "'{$column}' => [\n            'type' => '{$columnType}',\n            'operators' => {$operatorsStr}\n        ]";
        }

        $allowedFiltersWithTypeStr = implode(",\n        ", $allowedFiltersWithType);
        $searchableFieldsStr = implode(",\n        ", $searchableFields);

        return str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ allowedFilters }}', '{{ searchableFields }}'],
            [$namespace, $className, $allowedFiltersWithTypeStr, $searchableFieldsStr],
            $stub
        );
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

        return Str::startsWith($model, 'App\\') ? $model : 'App\\Models\\' . $model;
    }
}
