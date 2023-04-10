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
        if (resolveSchema()->hasTable('database_tokens')) {
            return;
        }

        resolveSchema()->create('database_tokens', static function (Blueprint $table): void {
            $table->id();

            foreach (mustConfigArray('auth.providers') as $config) {
                \assert(\is_array($config));

                $model = $config['model'];

                \assert(\is_string($model));

                $table->foreignIdFor($model)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            }

            $table->char('hash', 64);

            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });
    }
};
