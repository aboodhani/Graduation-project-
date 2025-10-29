<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            // nullable لأن بعض الأقسام القديمة قد لا تحتوي عليه
            $table->foreignId('created_by')->nullable()->after('id')->constrained('users')->nullOnDelete();
            // إذا تريد index فقط بدون FK: $table->unsignedBigInteger('created_by')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by'); // Laravel 9+
            // أو بديل: $table->dropColumn('created_by');
        });
    }
};
