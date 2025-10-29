<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('submissions', 'assignment_id')) {
                $table->foreignId('assignment_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('submissions', 'student_id')) {
                $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('submissions', 'image_path')) {
                $table->string('image_path')->nullable()->after('file_path');
            }
            if (! Schema::hasColumn('submissions', 'label')) {
                $table->string('label')->nullable()->after('image_path');
            }
            if (! Schema::hasColumn('submissions', 'code')) {
                $table->integer('code')->nullable()->after('label');
            }
            if (! Schema::hasColumn('submissions', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('code');
            }
            if (! Schema::hasColumn('submissions', 'feedback')) {
                $table->text('feedback')->nullable()->after('submitted_at');
            }
        });
    }

    public function down()
    {
        Schema::table('submissions', function (Blueprint $table) {
            if (Schema::hasColumn('submissions', 'image_path')) {
                $table->dropColumn('image_path');
            }
            if (Schema::hasColumn('submissions', 'label')) {
                $table->dropColumn('label');
            }
            if (Schema::hasColumn('submissions', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('submissions', 'submitted_at')) {
                $table->dropColumn('submitted_at');
            }
            if (Schema::hasColumn('submissions', 'feedback')) {
                $table->dropColumn('feedback');
            }
            // do not drop FK columns unless you know what you're doing
        });
    }
};
