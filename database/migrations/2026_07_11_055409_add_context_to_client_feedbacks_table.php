<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_feedbacks', function (Blueprint $table) {
            $table->string('context_type')->nullable()->after('rating');
            $table->unsignedBigInteger('context_id')->nullable()->after('context_type');
        });
    }

    public function down(): void
    {
        Schema::table('client_feedbacks', function (Blueprint $table) {
            $table->dropColumn(['context_type', 'context_id']);
        });
    }
};
