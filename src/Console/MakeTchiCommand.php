<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Tomchochola\Laratchi\Exceptions\Panicker;
use Tomchochola\Laratchi\Support\Typer;

class MakeTchiCommand extends GeneratorCommand
{
    /**
     * @inheritDoc
     */
    protected $name = 'make:tchi';

    /**
     * @inheritDoc
     */
    protected $description = 'Make tchi command';

    /**
     * @inheritDoc
     */
    protected $type = 'Model';

    /**
     * @inheritDoc
     */
    public function handle(): ?bool
    {
        $modelName = $this->modelName();

        if ($this->isReservedName($modelName)) {
            $this->error(Panicker::message(__METHOD__, 'name is reserved by PHP', ['name' => $modelName]));

            return false;
        }

        $this->call('make:validity', ['name' => "{$modelName}Validity"]);

        $this->call('make:model', ['name' => $modelName, '--factory' => true, '--migration' => true]);

        $this->make("Database\\Seeders\\{$modelName}Seeder", "database/seeders/{$modelName}Seeder.php", 'seeder.stub');

        // $this->make("App\\Http\\Requests\\Api\\{$modelName}\\{$modelName}IndexRequest", null, 'request.index.stub');
        // $this->make("App\\Http\\Requests\\Api\\{$modelName}\\{$modelName}ShowRequest", null, 'request.show.stub');
        // $this->make("App\\Http\\Requests\\Api\\{$modelName}\\{$modelName}UpdateRequest", null, 'request.update.stub');
        // $this->make("App\\Http\\Requests\\Api\\{$modelName}\\{$modelName}StoreRequest", null, 'request.store.stub');
        // $this->make("App\\Http\\Requests\\Api\\{$modelName}\\{$modelName}DestroyRequest", null, 'request.destroy.stub');

        $this->make("App\\Http\\Controllers\\Api\\{$modelName}\\{$modelName}IndexController", null, 'controller.index.stub');
        $this->make("App\\Http\\Controllers\\Api\\{$modelName}\\{$modelName}ShowController", null, 'controller.show.stub');
        $this->make("App\\Http\\Controllers\\Api\\{$modelName}\\{$modelName}UpdateController", null, 'controller.update.stub');
        $this->make("App\\Http\\Controllers\\Api\\{$modelName}\\{$modelName}StoreController", null, 'controller.store.stub');
        $this->make("App\\Http\\Controllers\\Api\\{$modelName}\\{$modelName}DestroyController", null, 'controller.destroy.stub');

        $this->make(
            "Tests\\Feature\\App\\Http\\Controllers\\Api\\{$modelName}\\{$modelName}IndexControllerTest",
            $this->laravel->basePath("tests/Feature/App/Http/Controllers/Api/{$modelName}/{$modelName}IndexControllerTest.php"),
            'test.index.stub',
        );
        $this->make(
            "Tests\\Feature\\App\\Http\\Controllers\\Api\\{$modelName}\\{$modelName}ShowControllerTest",
            $this->laravel->basePath("tests/Feature/App/Http/Controllers/Api/{$modelName}/{$modelName}ShowControllerTest.php"),
            'test.show.stub',
        );
        $this->make(
            "Tests\\Feature\\App\\Http\\Controllers\\Api\\{$modelName}\\{$modelName}UpdateControllerTest",
            $this->laravel->basePath("tests/Feature/App/Http/Controllers/Api/{$modelName}/{$modelName}UpdateControllerTest.php"),
            'test.update.stub',
        );
        $this->make(
            "Tests\\Feature\\App\\Http\\Controllers\\Api\\{$modelName}\\{$modelName}StoreControllerTest",
            $this->laravel->basePath("tests/Feature/App/Http/Controllers/Api/{$modelName}/{$modelName}StoreControllerTest.php"),
            'test.store.stub',
        );
        $this->make(
            "Tests\\Feature\\App\\Http\\Controllers\\Api\\{$modelName}\\{$modelName}DestroyControllerTest",
            $this->laravel->basePath("tests/Feature/App/Http/Controllers/Api/{$modelName}/{$modelName}DestroyControllerTest.php"),
            'test.destroy.stub',
        );

        $this->modifyOpenApi();
        $this->modifyDatabaseSeeder();
        $this->modifyDatabaseSchema();
        $this->modifyRoutes();
        $this->modifyTestCase();

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function getStub(): string
    {
        return '';
    }

    /**
     * Make stub.
     */
    protected function make(string $qualifiedClassName, ?string $path, string $stubName): void
    {
        $stub = $this->files->get($this->laravel->basePath("stubs/{$stubName}"));

        $stub = $this->replaceNamespace($stub, $qualifiedClassName)->replaceClass($stub, $qualifiedClassName);

        $modelName = $this->modelName();
        $table = $this->tableName();
        $qualifiedModelName = $this->qualifyModel($modelName);

        $factoryName = "{$modelName}Factory";
        $qualifiedFactoryName = "Database\\Factories\\{$factoryName}";

        $seederName = "{$modelName}Seeder";
        $qualifiedSeederName = "Database\\Seeders\\{$seederName}";

        $validityName = "{$modelName}Validity";
        $qualifiedValidityName = "App\\Http\\Validation\\{$validityName}";

        $indexRequest = "{$modelName}IndexRequest";
        $qualifiedIndexRequest = "App\\Http\\Requests\\Api\\{$modelName}\\{$indexRequest}";

        $showRequest = "{$modelName}ShowRequest";
        $qualifiedShowRequest = "App\\Http\\Requests\\Api\\{$modelName}\\{$showRequest}";

        $updateRequest = "{$modelName}UpdateRequest";
        $qualifiedUpdateRequest = "App\\Http\\Requests\\Api\\{$modelName}\\{$updateRequest}";

        $storeRequest = "{$modelName}StoreRequest";
        $qualifiedStoreRequest = "App\\Http\\Requests\\Api\\{$modelName}\\{$storeRequest}";

        $destroyRequest = "{$modelName}DestroyRequest";
        $qualifiedDestroyRequest = "App\\Http\\Requests\\Api\\{$modelName}\\{$destroyRequest}";

        $qualifiedUserModel = $this->userProviderModel() ?? 'App\\Models\\User';

        $userModel = class_basename($qualifiedUserModel);

        $userModelFactoryName = "{$userModel}Factory";
        $qualifiedUserModelFactoryName = "Database\\Factories\\{$userModelFactoryName}";

        $indexController = "{$modelName}IndexController";
        $qualifiedIndexController = "App\\Http\\Controllers\\Api\\{$modelName}\\{$indexController}";

        $showController = "{$modelName}ShowController";
        $qualifiedShowController = "App\\Http\\Controllers\\Api\\{$modelName}\\{$showController}";

        $updateController = "{$modelName}UpdateController";
        $qualifiedUpdateController = "App\\Http\\Controllers\\Api\\{$modelName}\\{$updateController}";

        $storeController = "{$modelName}StoreController";
        $qualifiedStoreController = "App\\Http\\Controllers\\Api\\{$modelName}\\{$storeController}";

        $destroyController = "{$modelName}DestroyController";
        $qualifiedDestroyController = "App\\Http\\Controllers\\Api\\{$modelName}\\{$destroyController}";

        $stub = \str_replace('{{ model }}', $modelName, $stub);
        $stub = \str_replace('{{ namespacedModel }}', $qualifiedModelName, $stub);

        $stub = \str_replace('{{ factory }}', $factoryName, $stub);
        $stub = \str_replace('{{ namespacedFactory }}', $qualifiedFactoryName, $stub);

        $stub = \str_replace('{{ userModelFactory }}', $userModelFactoryName, $stub);
        $stub = \str_replace('{{ namespacedUserModelFactory }}', $qualifiedUserModelFactoryName, $stub);

        $stub = \str_replace('{{ seeder }}', $seederName, $stub);
        $stub = \str_replace('{{ namespacedSeeder }}', $qualifiedSeederName, $stub);

        $stub = \str_replace('{{ validity }}', $validityName, $stub);
        $stub = \str_replace('{{ namespacedValidity }}', $qualifiedValidityName, $stub);

        $stub = \str_replace('{{ userModel }}', $userModel, $stub);

        $stub = \str_replace('{{ table }}', $table, $stub);

        $stub = \str_replace('{{ indexController }}', $indexController, $stub);
        $stub = \str_replace('{{ namespacedIndexController }}', $qualifiedIndexController, $stub);

        $stub = \str_replace('{{ showController }}', $showController, $stub);
        $stub = \str_replace('{{ namespacedShowController }}', $qualifiedShowController, $stub);

        $stub = \str_replace('{{ updateController }}', $updateController, $stub);
        $stub = \str_replace('{{ namespacedUpdateController }}', $qualifiedUpdateController, $stub);

        $stub = \str_replace('{{ storeController }}', $storeController, $stub);
        $stub = \str_replace('{{ namespacedStoreController }}', $qualifiedStoreController, $stub);

        $stub = \str_replace('{{ destroyController }}', $destroyController, $stub);
        $stub = \str_replace('{{ namespacedDestroyController }}', $qualifiedDestroyController, $stub);

        $stub = \str_replace('{{ indexRequest }}', $indexRequest, $stub);
        $stub = \str_replace('{{ namespacedIndexRequest }}', $qualifiedIndexRequest, $stub);

        $stub = \str_replace('{{ showRequest }}', $showRequest, $stub);
        $stub = \str_replace('{{ namespacedShowRequest }}', $qualifiedShowRequest, $stub);

        $stub = \str_replace('{{ updateRequest }}', $updateRequest, $stub);
        $stub = \str_replace('{{ namespacedUpdateRequest }}', $qualifiedUpdateRequest, $stub);

        $stub = \str_replace('{{ storeRequest }}', $storeRequest, $stub);
        $stub = \str_replace('{{ namespacedStoreRequest }}', $qualifiedStoreRequest, $stub);

        $stub = \str_replace('{{ destroyRequest }}', $destroyRequest, $stub);
        $stub = \str_replace('{{ namespacedDestroyRequest }}', $qualifiedDestroyRequest, $stub);

        $path ??= $this->getPath($qualifiedClassName);

        $this->makeDirectory($path);

        $this->files->put($path, $this->sortImports($stub));
    }

    /**
     * Modify json api.
     */
    protected function modifyOpenApi(): void
    {
        $path = $this->laravel->basePath('public/docs/openapi_v1.json');

        if (! $this->files->exists($path)) {
            return;
        }

        $modelName = $this->modelName();
        $table = $this->tableName();

        $openApi = $this->files->get($path);

        $json = Typer::assertArray(\json_decode($openApi, true));

        $json['tags'] = \array_merge(Typer::assertArray($json['tags']), [
            'name' => $table,
            'description' => $modelName,
        ]);

        $json['paths']["/{$table}/index"] = [
            'get' => [
                'tags' => [$table],
                'summary' => "Index {$table}",
                'description' => "Index {$table}\n\nUser must be authenticated",
                'operationId' => "get_{$table}_index",
                'parameters' => [
                    [
                        'description' => 'sort',
                        'in' => 'query',
                        'name' => 'sort[]',
                        'schema' => [
                            'description' => 'sort',
                            'type' => 'array',
                            'items' => [
                                'description' => 'sort',
                                'type' => 'string',
                                'enum' => ['-id', 'id', '-created_at', 'created_at', '-updated_at', 'updated_at', '-title', 'title'],
                            ],
                        ],
                    ],
                    [
                        '$ref' => '#/components/parameters/filterId',
                    ],
                    [
                        '$ref' => '#/components/parameters/filterNotId',
                    ],
                    [
                        '$ref' => '#/components/parameters/filterSlug',
                    ],
                    [
                        '$ref' => '#/components/parameters/filterNotSlug',
                    ],
                    [
                        '$ref' => '#/components/parameters/filterSearch',
                    ],
                    [
                        '$ref' => '#/components/parameters/take',
                    ],
                    [
                        '$ref' => '#/components/parameters/mode',
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Successful operation',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'description' => 'response data',
                                    'type' => 'object',
                                    'required' => ['data'],
                                    'properties' => [
                                        'data' => [
                                            'oneOf' => [
                                                [
                                                    'description' => 'data',
                                                    'type' => 'array',
                                                    'items' => [
                                                        'oneOf' => [
                                                            [
                                                                'allOf' => [
                                                                    [
                                                                        '$ref' => '#/components/schemas/Resource',
                                                                    ],
                                                                    [
                                                                        'description' => "{$modelName} index schema",
                                                                        'type' => 'object',
                                                                        'required' => ['attributes'],
                                                                        'properties' => [
                                                                            'type' => [
                                                                                'enum' => [$table],
                                                                            ],
                                                                            'attributes' => [
                                                                                'description' => 'attributes',
                                                                                'type' => 'object',
                                                                                'required' => ['title'],
                                                                                'properties' => [
                                                                                    'title' => [
                                                                                        '$ref' => '#/components/schemas/string',
                                                                                    ],
                                                                                ],
                                                                            ],
                                                                        ],
                                                                    ],
                                                                ],
                                                            ],
                                                            [
                                                                'allOf' => [
                                                                    [
                                                                        '$ref' => '#/components/schemas/Resource',
                                                                    ],
                                                                    [
                                                                        'description' => "{$modelName} select schema",
                                                                        'type' => 'object',
                                                                        'required' => ['attributes'],
                                                                        'properties' => [
                                                                            'type' => [
                                                                                'enum' => [$table],
                                                                            ],
                                                                            'attributes' => [
                                                                                'description' => 'attributes',
                                                                                'type' => 'object',
                                                                                'required' => ['title'],
                                                                                'properties' => [
                                                                                    'title' => [
                                                                                        '$ref' => '#/components/schemas/string',
                                                                                    ],
                                                                                ],
                                                                            ],
                                                                        ],
                                                                    ],
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                                [
                                                    '$ref' => '#/components/schemas/unsignedBigInt',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => [
                        '$ref' => '#/components/responses/401',
                    ],
                    '422' => [
                        '$ref' => '#/components/responses/422',
                    ],
                ],
            ],
        ];

        $json['paths']["/{$table}/show"] = [
            'get' => [
                'tags' => [$table],
                'summary' => "Show {$table}",
                'description' => "Show {$table}\n\nUser must be authenticated",
                'operationId' => "get_{$table}_show",
                'parameters' => [
                    [
                        '$ref' => '#/components/parameters/id',
                    ],
                    [
                        '$ref' => '#/components/parameters/slug',
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Successful operation',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'description' => 'response data',
                                    'type' => 'object',
                                    'required' => ['data'],
                                    'properties' => [
                                        'data' => [
                                            'allOf' => [
                                                [
                                                    '$ref' => '#/components/schemas/Resource',
                                                ],
                                                [
                                                    'description' => "{$modelName} show schema",
                                                    'type' => 'object',
                                                    'required' => ['attributes'],
                                                    'properties' => [
                                                        'type' => [
                                                            'enum' => [$table],
                                                        ],
                                                        'attributes' => [
                                                            'description' => 'attributes',
                                                            'type' => 'object',
                                                            'required' => ['title'],
                                                            'properties' => [
                                                                'title' => [
                                                                    '$ref' => '#/components/schemas/string',
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => [
                        '$ref' => '#/components/responses/401',
                    ],
                    '422' => [
                        '$ref' => '#/components/responses/422',
                    ],
                ],
            ],
        ];

        $json['paths']["/{$table}/store"] = [
            'post' => [
                'tags' => [$table],
                'summary' => "Store {$table}",
                'description' => "Store {$table}\n\nUser must be authenticated",
                'operationId' => "post_{$table}_store",
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'multipart/form-data' => [
                            'schema' => [
                                'description' => 'request data',
                                'type' => 'object',
                                'required' => ['data'],
                                'properties' => [
                                    'data' => [
                                        'description' => 'data',
                                        'type' => 'array',
                                        'items' => [
                                            'description' => 'item',
                                            'type' => 'object',
                                            'required' => ['title'],
                                            'properties' => [
                                                'title' => [
                                                    '$ref' => '#/components/schemas/varchar',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        '$ref' => '#/components/responses/Resources',
                    ],
                    '401' => [
                        '$ref' => '#/components/responses/401',
                    ],
                    '422' => [
                        '$ref' => '#/components/responses/422',
                    ],
                ],
            ],
        ];

        $json['paths']["/{$table}/update"] = [
            'post' => [
                'tags' => [$table],
                'summary' => "Update {$table}",
                'description' => "Update {$table}\n\nUser must be authenticated",
                'operationId' => "post_{$table}_update",
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'multipart/form-data' => [
                            'schema' => [
                                'description' => 'request data',
                                'type' => 'object',
                                'required' => ['data'],
                                'properties' => [
                                    'data' => [
                                        'description' => 'data',
                                        'type' => 'array',
                                        'items' => [
                                            'description' => 'item',
                                            'type' => 'object',
                                            'required' => ['id', 'title'],
                                            'properties' => [
                                                'id' => [
                                                    '$ref' => '#/components/schemas/id',
                                                ],
                                                'title' => [
                                                    '$ref' => '#/components/schemas/varchar',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '204' => [
                        '$ref' => '#/components/responses/204',
                    ],
                    '401' => [
                        '$ref' => '#/components/responses/401',
                    ],
                    '422' => [
                        '$ref' => '#/components/responses/422',
                    ],
                ],
            ],
        ];

        $json['paths']["/{$table}/destroy"] = [
            'post' => [
                'tags' => [$table],
                'summary' => "Destroy {$table}",
                'description' => "Destroy {$table}\n\nUser must be authenticated",
                'operationId' => "post_{$table}_destroy",
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'multipart/form-data' => [
                            'schema' => [
                                'description' => 'request data',
                                'type' => 'object',
                                'required' => ['data'],
                                'properties' => [
                                    'data' => [
                                        'description' => 'data',
                                        'type' => 'array',
                                        'items' => [
                                            'description' => 'item',
                                            'type' => 'object',
                                            'required' => ['id'],
                                            'properties' => [
                                                'id' => [
                                                    '$ref' => '#/components/schemas/id',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '204' => [
                        '$ref' => '#/components/responses/204',
                    ],
                    '401' => [
                        '$ref' => '#/components/responses/401',
                    ],
                    '422' => [
                        '$ref' => '#/components/responses/422',
                    ],
                ],
            ],
        ];

        $json['components']['schemas']["{$modelName}Embed"] = [
            'allOf' => [
                [
                    '$ref' => '#/components/schemas/Resource',
                ],
                [
                    'description' => "{$modelName} embed schema",
                    'type' => 'object',
                    'required' => ['attributes'],
                    'properties' => [
                        'type' => [
                            'enum' => [$table],
                        ],
                        'attributes' => [
                            'description' => 'attributes',
                            'type' => 'object',
                            'required' => ['title'],
                            'properties' => [
                                'title' => [
                                    '$ref' => '#/components/schemas/string',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        if (! isset($json['components']['parameters']['filterId'])) {
            $json['components']['parameters']['filterId'] = [
                'description' => 'id filter',
                'in' => 'query',
                'name' => 'filter[id][]',
                'schema' => [
                    'description' => 'id',
                    'type' => 'array',
                    'items' => [
                        '$ref' => '#/components/schemas/id',
                    ],
                ],
            ];
        }

        if (! isset($json['components']['parameters']['filterNotId'])) {
            $json['components']['parameters']['filterNotId'] = [
                'description' => 'not id filter',
                'in' => 'query',
                'name' => 'filter[not_id][]',
                'schema' => [
                    'description' => 'not id',
                    'type' => 'array',
                    'items' => [
                        '$ref' => '#/components/schemas/id',
                    ],
                ],
            ];
        }

        if (! isset($json['components']['parameters']['filterSlug'])) {
            $json['components']['parameters']['filterSlug'] = [
                'description' => 'slug filter',
                'in' => 'query',
                'name' => 'filter[slug][]',
                'schema' => [
                    'description' => 'slug',
                    'type' => 'array',
                    'items' => [
                        '$ref' => '#/components/schemas/slug',
                    ],
                ],
            ];
        }

        if (! isset($json['components']['parameters']['filterNotSlug'])) {
            $json['components']['parameters']['filterNotSlug'] = [
                'description' => 'not slug filter',
                'in' => 'query',
                'name' => 'filter[not_slug][]',
                'schema' => [
                    'description' => 'not slug',
                    'type' => 'array',
                    'items' => [
                        '$ref' => '#/components/schemas/slug',
                    ],
                ],
            ];
        }

        if (! isset($json['components']['parameters']['mode'])) {
            $json['components']['parameters']['mode'] = [
                'description' => 'mode',
                'in' => 'query',
                'name' => 'mode',
                'schema' => [
                    'description' => '0 = default, 1 = select, 2 = count',
                    'type' => 'integer',
                    'enum' => [0, 1, 2],
                ],
            ];
        }

        $json = Typer::assertString(\json_encode($json, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE));

        $this->files->put($path, $json);
    }

    /**
     * Get table name.
     */
    protected function tableName(): string
    {
        return Str::snake(Str::pluralStudly(class_basename($this->modelName())));
    }

    /**
     * Get model name.
     */
    protected function modelName(): string
    {
        return $this->getNameInput();
    }

    /**
     * Modify database seeder.
     */
    protected function modifyDatabaseSeeder(): void
    {
        $path = $this->laravel->databasePath('seeders/DatabaseSeeder.php');

        if (! $this->files->exists($path)) {
            return;
        }

        $modelName = $this->modelName();

        $databaseSeeder = $this->files->get($path);

        if (! \str_contains($databaseSeeder, 'public function run(): void')) {
            $databaseSeeder = \str_replace("{\n", "{\n    /**\n     * @inheritDoc\n     */\n    public function run(): void\n    {\n    }\n", $databaseSeeder);
        }

        $databaseSeeder = \str_replace(
            "public function run(): void\n    {\n",
            "public function run(): void\n    {\n        \$this->callOnce({$modelName}Seeder::class);\n",
            $databaseSeeder,
        );

        $this->files->put($path, $databaseSeeder);
    }

    /**
     * Modify database schema.
     */
    protected function modifyDatabaseSchema(): void
    {
        $path = $this->laravel->basePath('docs/database_schema.md');

        if (! $this->files->exists($path)) {
            return;
        }

        $table = $this->tableName();

        $databaseSchema = $this->files->get($path);

        $databaseSchema = \str_replace(
            'erDiagram',
            "erDiagram\n\n{$table} {\n  id id PK\n  id user_id FK \"users.id cascadeOnUpdate cascadeOnDelete\"\n  string title \"fulltext\"\n  timestamp created_at\n  timestamp updated_at\n}",
            $databaseSchema,
        );

        $this->files->put($path, $databaseSchema);
    }

    /**
     * Modify routes.
     */
    protected function modifyRoutes(): void
    {
        $path = $this->laravel->basePath('routes/api.php');

        if (! $this->files->exists($path)) {
            return;
        }

        $modelName = $this->modelName();
        $table = $this->tableName();

        $routes = $this->files->get($path);

        $routes = \str_replace(
            'declare(strict_types=1);',
            "declare(strict_types=1);\n\nresolveRouteRegistrar()\n    ->prefix('v1/{$table}')\n    ->group(static function (): void {\n        resolveRouteRegistrar()->post('store', App\\Http\\Controllers\\Api\\{$modelName}\\{$modelName}StoreController::class);\n        resolveRouteRegistrar()->get('index', App\\Http\\Controllers\\Api\\{$modelName}\\{$modelName}IndexController::class);\n        resolveRouteRegistrar()->get('show', App\\Http\\Controllers\\Api\\{$modelName}\\{$modelName}ShowController::class);\n        resolveRouteRegistrar()->post('update', App\\Http\\Controllers\\Api\\{$modelName}\\{$modelName}UpdateController::class);\n        resolveRouteRegistrar()->post('destroy', App\\Http\\Controllers\\Api\\{$modelName}\\{$modelName}DestroyController::class);\n    });",
            $routes,
        );

        $this->files->put($path, $routes);
    }

    /**
     * Modify test case.
     */
    protected function modifyTestCase(): void
    {
        $path = $this->laravel->basePath('tests/TestCase.php');

        if (! $this->files->exists($path)) {
            return;
        }

        $modelName = $this->modelName();
        $table = $this->tableName();

        $validityName = "{$modelName}Validity";
        $qualifiedValidityName = "App\\Http\\Validation\\{$validityName}";

        $testCase = $this->files->get($path);

        $testCase = \str_replace(
            "\n}\n",
            "\n\n    /**\n     * {$modelName} embed structure.\n     */\n    protected function structure{$modelName}Embed(): JsonApiValidator\n    {\n        \$validity = new \\{$qualifiedValidityName}();\n\n        return \$this->structure('{$table}', [\n            'title' => \$validity->title()->required(),\n        ]);\n    }\n}\n",
            $testCase,
        );

        $this->files->put($path, $testCase);
    }
}
