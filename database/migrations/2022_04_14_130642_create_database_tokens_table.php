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
        resolveSchema()->create('database_tokens', static function (Blueprint $table): void {
            $table->id();

            $table->string('provider');
            $table->string('auth_id');
            $table->char('hash', 64);

            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });
    }
};
