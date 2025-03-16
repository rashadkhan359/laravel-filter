<?php

namespace RashadKhan\LaravelFilter\Adapters;

use Illuminate\Support\Facades\Schema;


class EloquentSchemaAdapter
{
    /**
     * Get columns for the model.
     *
     * @param string $table
     * @return array
     */
    public function getColumns(string $table, ): array
    {
        return Schema::getColumnListing($table);

    }

    /**
     * Get column types for the model.
     *
     * @param string $table
     * @param string $column
     * @return string
     */
    public function getColumnType(string $table, string $column): string
    {
        return Schema::getColumnType($table, $column);
    }
}
