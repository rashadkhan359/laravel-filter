<?php

namespace RashadKhan\LaravelFilter\Adapters;

use Illuminate\Support\Facades\Config;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Command;
use MongoDB\Driver\Exception\Exception;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class MongoSchemaAdapter
{

    public function getColumns($table)
    {
        return array_keys($this->getColumnTypes($table));
    }

    /**
     * Get column types for a MongoDB collection.
     *
     * @param string $collection
     * @return array
     */
    public function getColumnTypes(string $collection): array
    {
        $config = Config::get('database.connections.mongodb');
        $columnTypes = [];

        try {
            $manager = $this->createMongoManager($config);
            $document = $this->sampleCollectionDocument($manager, $config['database'], $collection);

            if ($document) {
                foreach ($document as $field => $value) {
                    $columnTypes[$field] = $this->getMongoDataType($value);
                }
            }
        } catch (Exception $e) {
            \Log::error("MongoDB schema detection error: " . $e->getMessage());
        }

        return $columnTypes;
    }

    /**
     * Create MongoDB Manager instance from config.
     */
    protected function createMongoManager(array $config): Manager
    {
        $uri = $this->buildMongoUri($config);
        return new Manager($uri, [], ['typeMap' => ['root' => 'array', 'document' => 'array']]);
    }

    /**
     * Build MongoDB connection URI from config.
     */
    protected function buildMongoUri(array $config): string
    {
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? 27017;
        $username = $config['username'] ?? null;
        $password = $config['password'] ?? null;

        $uri = 'mongodb://';
        if ($username && $password) {
            $uri .= urlencode($username) . ':' . urlencode($password) . '@';
        }
        $uri .= $host . ':' . $port;

        if (!empty($config['options'])) {
            $uri .= '/?' . http_build_query($config['options']);
        }

        return $uri;
    }

    /**
     * Sample a document from the collection.
     */
    protected function sampleCollectionDocument(Manager $manager, string $database, string $collection): ?array
    {
        $command = new Command([
            'aggregate' => $collection,
            'pipeline' => [['$sample' => ['size' => 1]]],
            'cursor' => new \stdClass(),
        ]);

        $cursor = $manager->executeCommand($database, $command);
        return $cursor->toArray()[0] ?? null;
    }

    /**
     * Determine MongoDB data type with extended type checking.
     */
    protected function getMongoDataType($value): string
    {
        switch (true) {
            case is_int($value):
                return 'integer';
            case is_float($value):
                return 'double';
            case is_string($value):
                return 'string';
            case is_bool($value):
                return 'boolean';
            case is_array($value):
                return 'array';
            case $value instanceof ObjectId:
                return 'objectId';
            case $value instanceof UTCDateTime:
                return 'date';
            case $value instanceof \stdClass:
                return 'object';
            default:
                return gettype($value);
        }
    }
}
