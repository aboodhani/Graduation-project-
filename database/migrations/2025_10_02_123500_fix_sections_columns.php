<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // اجعل name قابل للـ NULL و teacher_id قابل للـ NULL كذلك (لو موجود)
        Schema::table('sections', function (Blueprint $table) {
            // تعديل نوع العمود لnullable — نستخدم statement raw لأن بعض إصدارات MySQL تتطلب MODIFY
        });

        // استخدم SQL مباشر لتعديل الأعمدة بأمان
        $tableName = 'sections';

        // ملء name من title إن كانت فارغة
        try {
            DB::statement("UPDATE `{$tableName}` SET `name` = `title` WHERE (`name` IS NULL OR `name` = '') AND (`title` IS NOT NULL AND `title` <> '')");
        } catch (\Throwable $e) { /* ignore */ }

        // تعديل عمود name إلى NULLABLE
        try {
            DB::statement("ALTER TABLE `{$tableName}` MODIFY `name` VARCHAR(191) NULL");
        } catch (\Throwable $e) { /* ignore */ }

        // تعديل teacher_id إلى nullable إن كان موجودًا وغير قابل للـ NULL
        try {
            DB::statement("ALTER TABLE `{$tableName}` MODIFY `teacher_id` BIGINT UNSIGNED NULL");
        } catch (\Throwable $e) { /* ignore */ }
    }

    public function down(): void
    {
        // محاولة إعادة الوضع السابق — نلغي التعديلات فقط إذا أمكن
        $tableName = 'sections';
        try {
            DB::statement("ALTER TABLE `{$tableName}` MODIFY `name` VARCHAR(191) NOT NULL");
        } catch (\Throwable $e) { /* ignore */ }

        try {
            DB::statement("ALTER TABLE `{$tableName}` MODIFY `teacher_id` BIGINT UNSIGNED NOT NULL");
        } catch (\Throwable $e) { /* ignore */ }
    }
};
