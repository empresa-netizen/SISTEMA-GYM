<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_logbooks', function (Blueprint $table) {
            $table->dateTime('logged_at')->change();
        });
    }

    public function down(): void
    {
        Schema::table('member_logbooks', function (Blueprint $table) {
            $table->date('logged_at')->change();
        });
    }
};
