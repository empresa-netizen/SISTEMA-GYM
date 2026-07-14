<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diet_meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diet_menu_id')->constrained('diet_menus')->cascadeOnDelete();
            $table->string('name');
            $table->string('time_label')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('diet_meal_foods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diet_meal_id')->constrained('diet_meals')->cascadeOnDelete();
            $table->foreignId('diet_food_id')->constrained('diet_foods')->cascadeOnDelete();
            $table->decimal('quantity_in_grams', 8, 2);
            $table->unsignedSmallInteger('order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['diet_meal_id', 'diet_food_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diet_meal_foods');
        Schema::dropIfExists('diet_meals');
    }
};
