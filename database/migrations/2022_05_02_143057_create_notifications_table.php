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
        if (resolveSchema()->hasTable('notifications')) {
            return;
        }

        resolveSchema()->create('notifications', static function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');

            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });
    }
};
