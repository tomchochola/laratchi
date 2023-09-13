<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Tomchochola\Laratchi\Database\Migrator;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Migrator::createPasswordResetsTables();
    }
};
