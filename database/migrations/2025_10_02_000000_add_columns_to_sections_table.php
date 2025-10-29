<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // لو الجدول غير موجود ننشئه بالكامل (حالة نادرة)
        if (! Schema::hasTable('sections')) {
            Schema::create('sections', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });

            return;
        }

        // لو الجدول موجود نضيف الأعمدة الناقصة بأمان
        Schema::table('sections', function (Blueprint $table) {
            if (! Schema::hasColumn('sections', 'name')) {
                $table->string('name')->nullable()->after('id');
            }

            if (! Schema::hasColumn('sections', 'title')) {
                $table->string('title')->nullable()->after('name');
            }

            if (! Schema::hasColumn('sections', 'description')) {
                $table->text('description')->nullable()->after('title');
            }

            if (! Schema::hasColumn('sections', 'created_by')) {
                // حاول إنشاء FK إن أمكن، وإلا أنشئ العمود فقط
                try {
                    $table->foreignId('created_by')->nullable()->after('description')->constrained('users')->nullOnDelete();
                } catch (\Throwable $e) {
                    // بعض قواعد البيانات أو إصدارات MySQL قد تعترض هنا أثناء تعديل الجداول؛
                    // نضمن على الأقل وجود العمود
                    if (! Schema::hasColumn('sections', 'created_by')) {
                        $table->unsignedBigInteger('created_by')->nullable()->after('description')->index();
                    }
                }
            }

            if (! Schema::hasColumn('sections', 'created_at') || ! Schema::hasColumn('sections', 'updated_at')) {
                $table->timestamps();
            }
        });

        // ملء عمود name من title إن كان فارغاً (نحاول بهدوء)
        try {
            DB::table('sections')->whereNull('name')->whereNotNull('title')->update(['name' => DB::raw('title')]);
        } catch (\Throwable $e) {
            // تجاهل أي خطأ أثناء backfill
        }
    }

    public function down(): void
    {
        // احذر من محاولة إسقاط أعمدة/قيود غير موجودة
        if (! Schema::hasTable('sections')) {
            return;
        }

        Schema::table('sections', function (Blueprint $table) {
            // drop FK 'created_by' بأمان
            if (Schema::hasColumn('sections', 'created_by')) {
                try {
                    $table->dropForeign(['created_by']);
                } catch (\Throwable $e) {
                    // قد لا يكون FK مسمى بالطريقة المتوقعة — تجاهل الأخطاء
                }

                try {
                    $table->dropColumn('created_by');
                } catch (\Throwable $e) {
                    // تجاهل إذا لم يُسقط
                }
            }

            if (Schema::hasColumn('sections', 'description')) {
                try { $table->dropColumn('description'); } catch (\Throwable $e) {}
            }

            if (Schema::hasColumn('sections', 'title')) {
                try { $table->dropColumn('title'); } catch (\Throwable $e) {}
            }

            if (Schema::hasColumn('sections', 'name')) {
                try { $table->dropColumn('name'); } catch (\Throwable $e) {}
            }

            // لا نحذف timestamps هنا لأننا قد نكون أضفناها على جدول قديم؛
            // لو أنشأت الجدول في هذا المايغريشن (حالة up() أنشأه) فتمت إزالته بالكامل بالـ dropIfExists بالأسفل
        });

        // لو أردنا حذف الجدول نهائياً إن كان أنشئ لأول مرة عبر هذا المايغريشن
        // لكن لتفادي حذف بيانات موجودة نترك الجدول كما هو.
    }
};
