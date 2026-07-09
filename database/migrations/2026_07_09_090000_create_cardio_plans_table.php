<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cardio_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('modality')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->string('intensity')->nullable();
            $table->unsignedTinyInteger('weekly_frequency')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('active');
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->timestamps();

            $table->index(['parent_id', 'member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cardio_plans');
    }
};
