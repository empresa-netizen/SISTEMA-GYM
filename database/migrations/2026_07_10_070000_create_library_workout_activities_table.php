<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_workout_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_workout_id')->constrained('library_workouts')->cascadeOnDelete();
            $table->string('exercise_name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sets')->nullable();
            $table->unsignedSmallInteger('reps')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->unsignedSmallInteger('rest_seconds')->nullable();
            $table->decimal('weight_kg', 8, 2)->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_workout_activities');
    }
};
