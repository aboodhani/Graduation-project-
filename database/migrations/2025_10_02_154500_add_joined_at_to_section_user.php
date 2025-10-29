<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('section_user')) {
            // لو ما عندك جدول المحوري أصلاً - أنشئ واحد بسيط
            Schema::create('section_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('joined_at')->nullable();
                $table->timestamps();
                $table->unique(['section_id','user_id']);
            });
            return;
        }

        // لو الجدول موجود، نضيف الأعمدة الناقصة بأمان
        Schema::table('section_user', function (Blueprint $table) {
            if (! Schema::hasColumn('section_user', 'joined_at')) {
                $table->timestamp('joined_at')->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('section_user', 'created_at') || ! Schema::hasColumn('section_user', 'updated_at')) {
                $table->timestamps();
            }
        });

        // Backfill: لو تحب تعبّي joined_at من created_at لو موجود
        try {
            DB::statement('UPDATE section_user SET joined_at = created_at WHERE (joined_at IS NULL OR joined_at = "") AND (created_at IS NOT NULL)');
        } catch (\Throwable $e) { /* ignore */ }
    }

    public function down(): void
    {
        if (! Schema::hasTable('section_user')) {
            return;
        }

        Schema::table('section_user', function (Blueprint $table) {
            if (Schema::hasColumn('section_user','joined_at')) {
                try { $table->dropColumn('joined_at'); } catch (\Throwable $e) {}
            }
            // لا نحذف timestamps تلقائياً لتجنب فقد بيانات
        });
    }
};
