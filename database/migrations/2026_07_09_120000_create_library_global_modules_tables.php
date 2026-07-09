<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_workouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('focus')->nullable();
            $table->unsignedTinyInteger('duration_weeks')->nullable();
            $table->unsignedTinyInteger('sessions_per_week')->nullable();
            $table->enum('level', ['beginner', 'intermediate', 'advanced'])->default('intermediate');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['parent_id', 'status']);
        });

        Schema::create('library_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('product')->nullable();
            $table->unsignedSmallInteger('modules_count')->default(0);
            $table->unsignedSmallInteger('lessons_count')->default(0);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['parent_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_courses');
        Schema::dropIfExists('library_workouts');
    }
};
