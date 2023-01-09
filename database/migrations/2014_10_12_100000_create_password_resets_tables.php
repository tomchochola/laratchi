<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (mustConfigArray('auth.passwords') as $config) {
            \assert(\is_array($config));

            $tableName = $config['table'];

            \assert(\is_string($tableName));

            if (resolveSchema()->hasTable($tableName)) {
                continue;
            }

            resolveSchema()->create($tableName, static function (Blueprint $table): void {
                $table->id();

                $table->string('email')->unique();
                $table->string('token');

                $table->timestamp('created_at');
            });
        }
    }
};
