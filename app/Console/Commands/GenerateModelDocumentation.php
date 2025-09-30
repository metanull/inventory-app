<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;

class GenerateModelDocumentation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docs:models {--force : Overwrite existing documentation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate comprehensive model documentation from Laravel models and database schema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $outputPath = base_path('docs/_model/index.md');

        // Check if file exists and --force not provided
        if (File::exists($outputPath) && ! $this->option('force')) {
            if (! $this->confirm('Documentation file already exists. Overwrite?')) {
                $this->info('Documentation generation cancelled.');

                return Command::SUCCESS;
            }
        }

        $this->info('Generating model documentation...');

        // Ensure output directory exists
        $outputDir = dirname($outputPath);
        if (! File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        // Get all models
        $models = $this->getAllModels();
        $this->info('Found '.count($models).' models');

        // Generate documentation
        $documentation = $this->generateDocumentation($models);

        // Write to file
        File::put($outputPath, $documentation);

        $this->info('✅ Model documentation generated successfully!');
        $this->info('📄 Output: '.$outputPath);

        return Command::SUCCESS;
    }

    /**
     * Get all model classes from the app/Models directory
     */
    private function getAllModels(): array
    {
        $models = [];
        $modelsPath = app_path('Models');

        if (! File::exists($modelsPath)) {
            return $models;
        }

        $files = File::allFiles($modelsPath);

        foreach ($files as $file) {
            $className = 'App\\Models\\'.$file->getFilenameWithoutExtension();

            if (class_exists($className)) {
                $reflection = new ReflectionClass($className);

                // Only include concrete model classes that extend Model
                if (! $reflection->isAbstract() && $reflection->isSubclassOf(Model::class)) {
                    $models[] = $className;
                }
            }
        }

        // Sort models alphabetically
        sort($models);

        return $models;
    }

    /**
     * Generate the complete documentation
     */
    private function generateDocumentation(array $models): string
    {
        $content = [];

        // Header
        $content[] = '---';
        $content[] = 'layout: default';
        $content[] = 'title: Generated Model Documentation';
        $content[] = '---';
        $content[] = '';
        $content[] = '# 🤖 Generated Model Documentation';
        $content[] = '';
        $content[] = '{: .highlight }';
        $content[] = '> This documentation is automatically generated from the Laravel models and database schema. Last updated: '.now()->format('Y-m-d H:i:s T');
        $content[] = '';

        // Overview
        $content[] = '## 📊 Overview';
        $content[] = '';
        $content[] = '- **📈 Total Models:** '.count($models);
        $content[] = '- **🗄️ Database Connection:** '.config('database.default');
        $content[] = '- **🔧 Laravel Version:** '.app()->version();
        $content[] = '';

        // Table of Contents
        $content[] = '## 📚 Table of Contents';
        $content[] = '';
        foreach ($models as $modelClass) {
            $modelName = class_basename($modelClass);
            $anchor = strtolower(str_replace('\\', '-', $modelName));
            $content[] = "- [{$modelName}](#{$anchor})";
        }
        $content[] = '';

        // Generate documentation for each model
        foreach ($models as $modelClass) {
            $modelDoc = $this->generateModelDocumentation($modelClass);
            $content = array_merge($content, $modelDoc);
            $content[] = '';
        }

        // Footer
        $content[] = '---';
        $content[] = '';
        $content[] = '🤖 *This documentation was automatically generated using `php artisan docs:models`*';

        return implode("\n", $content);
    }

    /**
     * Generate documentation for a single model
     */
    private function generateModelDocumentation(string $modelClass): array
    {
        $content = [];
        $modelName = class_basename($modelClass);
        $anchor = strtolower(str_replace('\\', '-', $modelName));

        try {
            /** @var Model $model */
            $model = new $modelClass;
            $reflection = new ReflectionClass($modelClass);

            // Model header
            $content[] = "## {$modelName} {#{$anchor}}";
            $content[] = '';
            $content[] = "**Namespace:** `{$modelClass}`";
            $content[] = '';

            // Table information
            $tableName = $model->getTable();
            $content[] = '### 🗄️ Database Table';
            $content[] = '';
            $content[] = '| Property | Value |';
            $content[] = '|----------|-------|';
            $content[] = "| **Table Name** | `{$tableName}` |";
            $content[] = "| **Primary Key** | `{$model->getKeyName()}` |";
            $content[] = '| **Key Type** | '.($model->getKeyType() === 'int' ? 'Auto-incrementing Integer' : 'String (UUID)').' |';
            $content[] = '| **Incrementing** | '.($model->getIncrementing() ? 'Yes' : 'No').' |';
            $content[] = '| **Timestamps** | '.($model->usesTimestamps() ? 'Yes (`created_at`, `updated_at`)' : 'No').' |';

            // Check for soft deletes
            if (method_exists($model, 'bootSoftDeletes')) {
                $content[] = '| **Soft Deletes** | Yes (`deleted_at`) |';
            }

            $content[] = '';

            // Database schema
            if (Schema::hasTable($tableName)) {
                $content[] = '### 🏗️ Database Schema';
                $content[] = '';
                $content[] = '| Column | Type | Nullable | Default | Extra |';
                $content[] = '|--------|------|----------|---------|-------|';

                $columns = Schema::getColumnListing($tableName);
                foreach ($columns as $column) {
                    $columnDetails = $this->getColumnDetails($tableName, $column);
                    $content[] = "| `{$column}` | {$columnDetails['type']} | {$columnDetails['nullable']} | {$columnDetails['default']} | {$columnDetails['extra']} |";
                }
                $content[] = '';
            }

            // Fillable fields
            $fillable = $model->getFillable();
            if (! empty($fillable)) {
                $content[] = '### ✏️ Fillable Fields';
                $content[] = '';
                $content[] = '```php';
                $content[] = "['".implode("', '", $fillable)."']";
                $content[] = '```';
                $content[] = '';
            }

            // Guarded fields
            $guarded = $model->getGuarded();
            if (! empty($guarded) && $guarded !== ['*']) {
                $content[] = '### 🔒 Guarded Fields';
                $content[] = '';
                $content[] = '```php';
                $content[] = "['".implode("', '", $guarded)."']";
                $content[] = '```';
                $content[] = '';
            }

            // Casts
            $casts = $model->getCasts();
            if (! empty($casts)) {
                $content[] = '### 🔄 Attribute Casting';
                $content[] = '';
                $content[] = '| Attribute | Cast Type |';
                $content[] = '|-----------|-----------|';
                foreach ($casts as $attribute => $castType) {
                    $content[] = "| `{$attribute}` | `{$castType}` |";
                }
                $content[] = '';
            }

            // Constants (if any)
            $constants = $reflection->getConstants();
            if (! empty($constants)) {
                $content[] = '### 📋 Model Constants';
                $content[] = '';
                $content[] = '```php';
                foreach ($constants as $name => $value) {
                    $valueStr = is_string($value) ? "'{$value}'" : (is_bool($value) ? ($value ? 'true' : 'false') : $value);
                    $content[] = "const {$name} = {$valueStr};";
                }
                $content[] = '```';
                $content[] = '';
            }

            // Relationships
            $relationships = $this->getModelRelationships($model, $reflection);
            if (! empty($relationships)) {
                $content[] = '### 🔗 Relationships';
                $content[] = '';
                foreach ($relationships as $type => $relations) {
                    if (! empty($relations)) {
                        $content[] = "#### {$type}";
                        foreach ($relations as $relation) {
                            $relatedModel = isset($relation['related']) ? class_basename($relation['related']) : 'Unknown';
                            $relatedAnchor = strtolower(str_replace('\\', '-', $relatedModel));
                            $content[] = "- **`{$relation['method']}()`**: {$relation['type']} [{$relatedModel}](#{$relatedAnchor})";
                        }
                        $content[] = '';
                    }
                }
            }

            // Scopes (if any)
            $scopes = $this->getModelScopes($reflection);
            if (! empty($scopes)) {
                $content[] = '### 🔍 Query Scopes';
                $content[] = '';
                foreach ($scopes as $scope) {
                    $content[] = "- **`{$scope}()`**";
                }
                $content[] = '';
            }

        } catch (\Exception $e) {
            $content[] = '⚠️ **Error generating documentation for this model:** '.$e->getMessage();
            $content[] = '';
        }

        return $content;
    }

    /**
     * Get column details from database schema
     */
    private function getColumnDetails(string $tableName, string $columnName): array
    {
        try {
            $columnType = Schema::getColumnType($tableName, $columnName);

            // This is a simplified approach - Laravel doesn't provide easy access to all column details
            // In a more complete implementation, you might use raw database queries
            return [
                'type' => $columnType,
                'nullable' => 'Unknown',
                'default' => 'Unknown',
                'extra' => '',
            ];
        } catch (\Exception $e) {
            return [
                'type' => 'Unknown',
                'nullable' => 'Unknown',
                'default' => 'Unknown',
                'extra' => '',
            ];
        }
    }

    /**
     * Get model relationships using reflection
     */
    private function getModelRelationships(Model $model, ReflectionClass $reflection): array
    {
        $relationships = [
            'Belongs To' => [],
            'Has Many' => [],
            'Has One' => [],
            'Belongs To Many' => [],
            'Has Many Through' => [],
            'Morph To' => [],
            'Morph Many' => [],
            'Morph One' => [],
        ];

        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $methodName = $method->getName();

            // Skip magic methods, accessors, mutators, and other non-relationship methods
            if ($method->isStatic() ||
                $method->getDeclaringClass()->getName() !== $reflection->getName() ||
                $method->getNumberOfRequiredParameters() > 0 || // Skip methods that require parameters
                str_starts_with($methodName, 'get') ||
                str_starts_with($methodName, 'set') ||
                str_starts_with($methodName, '_') ||
                str_contains($methodName, 'Attribute') ||
                in_array($methodName, [
                    'boot', 'booted', 'save', 'delete', 'update', 'create', 'find', 'fresh',
                    'refresh', 'replicate', 'touch', 'push', 'increment', 'decrement',
                    'resolveRouteBinding', 'resolveRouteBindingQuery', 'resolveChildRouteBinding',
                    'bootTraits', 'initializeTraits', 'clearBootedModels', 'unsetEventDispatcher',
                    'toArray', 'toJson', 'jsonSerialize', 'getKey', 'getKeyName', 'getKeyType',
                    'getIncrementing', 'getTable', 'usesTimestamps', 'getCreatedAtColumn',
                    'getUpdatedAtColumn', 'getFillable', 'getGuarded', 'getCasts', 'getDates',
                    'getDateFormat', 'getHidden', 'getVisible', 'getAppends', 'hasGetMutator',
                    'hasSetMutator', 'hasAttributeMutator', 'hasAccessorOrMutator',
                ])) {
                continue;
            }

            try {
                // Try to call the method to see if it returns a relationship
                $result = $model->{$methodName}();

                if ($result instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
                    $relationType = class_basename(get_class($result));
                    $relatedModel = get_class($result->getRelated());

                    $relationshipType = match ($relationType) {
                        'BelongsTo' => 'Belongs To',
                        'HasMany' => 'Has Many',
                        'HasOne' => 'Has One',
                        'BelongsToMany' => 'Belongs To Many',
                        'HasManyThrough' => 'Has Many Through',
                        'MorphTo' => 'Morph To',
                        'MorphMany' => 'Morph Many',
                        'MorphOne' => 'Morph One',
                        default => 'Other'
                    };

                    if (isset($relationships[$relationshipType])) {
                        $relationships[$relationshipType][] = [
                            'method' => $methodName,
                            'type' => $relationType,
                            'related' => $relatedModel,
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Ignore methods that can't be called or don't return relationships
                continue;
            }
        }

        return $relationships;
    }

    /**
     * Get model scopes using reflection
     */
    private function getModelScopes(ReflectionClass $reflection): array
    {
        $scopes = [];
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            if (str_starts_with($method->getName(), 'scope') &&
                $method->getDeclaringClass()->getName() === $reflection->getName()) {
                // Convert scopeFooBar to fooBar
                $scopeName = lcfirst(substr($method->getName(), 5));
                $scopes[] = $scopeName;
            }
        }

        return $scopes;
    }
}
