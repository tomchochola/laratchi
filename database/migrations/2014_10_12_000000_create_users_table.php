<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Tomchochola\Laratchi\Auth\Actions\CycleRememberTokenAction;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (resolveSchema()->hasTable('users')) {
            return;
        }

        resolveSchema()->create('users', static function (Blueprint $table): void {
            $table->id();

            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->char('remember_token', CycleRememberTokenAction::$rememberTokenLength)->nullable();
            $table->char('locale', 2);

            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });
    }
};
