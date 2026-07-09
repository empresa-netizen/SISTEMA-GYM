<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('member_photos')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE member_photos MODIFY COLUMN type ENUM('front','back','side','progress','document','exam_document') NOT NULL DEFAULT 'progress'");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('member_photos')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::table('member_photos')->where('type', 'exam_document')->update(['type' => 'document']);
            DB::statement("ALTER TABLE member_photos MODIFY COLUMN type ENUM('front','back','side','progress','document') NOT NULL DEFAULT 'progress'");
        }
    }
};
