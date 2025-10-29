<?php

// database/migrations/2025_10_02_000000_add_ai_fields_to_submissions.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('submissions','file_path')) {
                $table->string('file_path')->nullable()->after('content');
            }
            if (! Schema::hasColumn('submissions','submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('file_path');
            }
            if (! Schema::hasColumn('submissions','label')) {
                $table->string('label')->nullable()->after('feedback');
            }
            if (! Schema::hasColumn('submissions','code')) {
                $table->integer('code')->nullable()->after('label');
            }
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $cols = ['file_path','submitted_at','label','code'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('submissions', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
