<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_logbooks', function (Blueprint $table) {
            $table->decimal('numeric_value', 10, 2)->nullable()->after('rating');
            $table->string('unit', 20)->nullable()->after('numeric_value');
            $table->json('metadata')->nullable()->after('unit');
        });
    }

    public function down(): void
    {
        Schema::table('member_logbooks', function (Blueprint $table) {
            $table->dropColumn(['numeric_value', 'unit', 'metadata']);
        });
    }
};
