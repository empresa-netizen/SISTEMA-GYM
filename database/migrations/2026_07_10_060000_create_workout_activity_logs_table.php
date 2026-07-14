<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('workout_id')->constrained('workouts')->cascadeOnDelete();
            $table->foreignId('workout_activity_id')->constrained('workout_activities')->cascadeOnDelete();
            $table->unsignedTinyInteger('sets')->nullable();
            $table->unsignedSmallInteger('reps')->nullable();
            $table->decimal('weight_kg', 8, 2)->nullable();
            $table->boolean('is_completed')->default(true);
            $table->text('notes')->nullable();
            $table->timestamp('logged_at');
            $table->timestamps();

            $table->index(['member_id', 'logged_at']);
            $table->index(['workout_id', 'workout_activity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_activity_logs');
    }
};
