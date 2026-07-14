<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL enum expansion — overdue is the explicit late state after finance:check-overdue.
        DB::statement("ALTER TABLE invoices MODIFY status ENUM('paid','unpaid','partially_paid','cancelled','overdue') NOT NULL DEFAULT 'unpaid'");
    }

    public function down(): void
    {
        DB::table('invoices')->where('status', 'overdue')->update(['status' => 'unpaid']);
        DB::statement("ALTER TABLE invoices MODIFY status ENUM('paid','unpaid','partially_paid','cancelled') NOT NULL DEFAULT 'unpaid'");
    }
};
