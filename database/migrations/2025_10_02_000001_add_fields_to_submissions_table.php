<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('submissions', 'student_id')) {
                $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete()->after('id');
            }
            if (! Schema::hasColumn('submissions', 'assignment_id')) {
                $table->foreignId('assignment_id')->nullable()->constrained('assignments')->nullOnDelete()->after('student_id');
            }
            if (! Schema::hasColumn('submissions', 'section_id')) {
                $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete()->after('assignment_id');
            }
            if (! Schema::hasColumn('submissions', 'file_path')) {
                $table->string('file_path')->nullable()->after('section_id');
            }
            if (! Schema::hasColumn('submissions', 'label')) {
                $table->string('label')->nullable()->after('file_path');
            }
            if (! Schema::hasColumn('submissions', 'code')) {
                $table->integer('code')->nullable()->after('label');
            }
            if (! Schema::hasColumn('submissions', 'feedback')) {
                $table->text('feedback')->nullable()->after('code');
            }
            if (! Schema::hasColumn('submissions', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('feedback');
            }
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            foreach (['submitted_at','feedback','code','label','file_path','section_id','assignment_id','student_id'] as $col) {
                if (Schema::hasColumn('submissions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
