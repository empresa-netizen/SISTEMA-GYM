<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->timestamp('noted_at')->useCurrent();
            $table->timestamps();

            $table->index(['member_id', 'noted_at']);
        });

        if (Schema::hasColumn('members', 'notes')) {
            $rows = DB::table('members')
                ->select('id', 'parent_id', 'notes', 'updated_at', 'created_at')
                ->whereNotNull('notes')
                ->where('notes', '!=', '')
                ->get();

            foreach ($rows as $row) {
                DB::table('member_notes')->insert([
                    'parent_id' => $row->parent_id,
                    'member_id' => $row->id,
                    'author_id' => null,
                    'body' => $row->notes,
                    'noted_at' => $row->updated_at ?? $row->created_at ?? now(),
                    'created_at' => $row->updated_at ?? $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? $row->created_at ?? now(),
                ]);
            }

            Schema::table('members', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('members', 'notes')) {
            Schema::table('members', function (Blueprint $table) {
                $table->text('notes')->nullable()->after('status');
            });
        }

        if (Schema::hasTable('member_notes')) {
            $notes = DB::table('member_notes')
                ->select('member_id', 'body', 'noted_at')
                ->orderByDesc('noted_at')
                ->get()
                ->groupBy('member_id');

            foreach ($notes as $memberId => $items) {
                $latest = $items->first();
                DB::table('members')->where('id', $memberId)->update([
                    'notes' => $latest->body,
                ]);
            }

            Schema::dropIfExists('member_notes');
        }
    }
};
