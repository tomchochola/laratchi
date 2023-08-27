<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Tomchochola\Laratchi\Config\Config;
use Tomchochola\Laratchi\Support\Resolver;

class Migrator
{
    /**
     * Create users table.
     */
    public static function createUsersTable(): void
    {
        $schema = Resolver::resolveSchemaBuilder();

        if ($schema->hasTable('users')) {
            return;
        }

        $schema->create('users', static function (Blueprint $table): void {
            $table->id();

            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->char('locale', 2);

            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });
    }

    /**
     * Create password resets tables.
     */
    public static function createPasswordResetsTables(): void
    {
        $config = Config::inject();

        foreach ($config->parsers('auth.passwords') as $parser) {
            static::createPasswordResetsTable($parser->assertString('table'));
        }
    }

    /**
     * Create database tokens table.
     */
    public static function createDatabaseTokensTable(): void
    {
        $schema = Resolver::resolveSchemaBuilder();

        if ($schema->hasTable('database_tokens')) {
            return;
        }

        $schema->create('database_tokens', static function (Blueprint $table): void {
            $table->id();

            $table->char('hash', 64);

            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });
    }

    /**
     * Create database tokens columns.
     */
    public static function createDatabaseTokensColumns(): void
    {
        $config = Config::inject();

        foreach ($config->parsers('auth.providers') as $parser) {
            static::createDatabaseTokensColumn(new ($parser->assertA('model', Model::class))());
        }
    }

    /**
     * Create password resets table.
     */
    public static function createPasswordResetsTable(string $table): void
    {
        $schema = Resolver::resolveSchemaBuilder();

        if ($schema->hasTable($table)) {
            return;
        }

        $schema->create($table, static function (Blueprint $table): void {
            $table->id();

            $table->string('email')->unique();
            $table->string('token');

            $table->timestamp('created_at');
        });
    }

    /**
     * Create database tokens column.
     */
    public static function createDatabaseTokensColumn(Model $model): void
    {
        $schema = Resolver::resolveSchemaBuilder();

        if ($schema->hasColumn('database_tokens', $model->getForeignKey())) {
            return;
        }

        $schema->table('database_tokens', static function (Blueprint $table) use ($model): void {
            $table
                ->foreignIdFor($model)
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }
}
