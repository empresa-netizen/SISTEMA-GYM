<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'member_id')) {
                $table->foreignId('member_id')->nullable()->after('parent_id')->constrained()->nullOnDelete();
            }
        });

        Schema::create('member_anamneses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->text('goals')->nullable();
            $table->text('injuries')->nullable();
            $table->text('medications')->nullable();
            $table->text('lifestyle')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamps();
            $table->unique(['parent_id', 'member_id']);
        });

        Schema::create('member_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->enum('type', ['front', 'back', 'side', 'progress', 'document'])->default('progress');
            $table->string('caption')->nullable();
            $table->timestamps();
        });

        Schema::create('member_logbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['TRAINING', 'DIET', 'WEIGHT'])->default('TRAINING');
            $table->string('title');
            $table->date('logged_at');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        Schema::create('diet_prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('diet_menu_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'sent'])->default('draft');
            $table->enum('delivery_status', ['PENDING', 'DELIVERED', 'LATE'])->default('PENDING');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diet_prescriptions');
        Schema::dropIfExists('member_logbooks');
        Schema::dropIfExists('member_photos');
        Schema::dropIfExists('member_anamneses');
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'member_id')) {
                $table->dropConstrainedForeignId('member_id');
            }
        });
    }
};
