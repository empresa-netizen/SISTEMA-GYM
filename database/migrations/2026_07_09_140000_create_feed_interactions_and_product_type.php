<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coach_feed_items', function (Blueprint $table) {
            if (! Schema::hasColumn('coach_feed_items', 'image_path')) {
                $table->string('image_path')->nullable()->after('meta');
            }
            if (! Schema::hasColumn('coach_feed_items', 'author_id')) {
                $table->foreignId('author_id')->nullable()->after('parent_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('coach_feed_items', 'likes_count')) {
                $table->unsignedInteger('likes_count')->default(0)->after('image_path');
            }
            if (! Schema::hasColumn('coach_feed_items', 'comments_count')) {
                $table->unsignedInteger('comments_count')->default(0)->after('likes_count');
            }
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE coach_feed_items MODIFY COLUMN type ENUM('POST','NEWS','DELIVERY_LATE','CONVERSATION','FEEDBACK') NOT NULL DEFAULT 'POST'");
        }

        Schema::create('feed_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('coach_feed_item_id')->constrained('coach_feed_items')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['coach_feed_item_id', 'user_id']);
        });

        Schema::create('feed_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('coach_feed_item_id')->constrained('coach_feed_items')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['coach_feed_item_id', 'created_at']);
        });

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'type')) {
                $table->string('type')->default('service')->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'type')) {
                $table->dropColumn('type');
            }
        });

        Schema::dropIfExists('feed_comments');
        Schema::dropIfExists('feed_likes');

        Schema::table('coach_feed_items', function (Blueprint $table) {
            if (Schema::hasColumn('coach_feed_items', 'comments_count')) {
                $table->dropColumn('comments_count');
            }
            if (Schema::hasColumn('coach_feed_items', 'likes_count')) {
                $table->dropColumn('likes_count');
            }
            if (Schema::hasColumn('coach_feed_items', 'image_path')) {
                $table->dropColumn('image_path');
            }
            if (Schema::hasColumn('coach_feed_items', 'author_id')) {
                $table->dropConstrainedForeignId('author_id');
            }
        });
    }
};
