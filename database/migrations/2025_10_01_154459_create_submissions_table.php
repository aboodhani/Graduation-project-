<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('submissions')) {
            Schema::create('submissions', function (Blueprint $table) {
                $table->id();
                // adjust FKs and names to your domain model
                $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->text('content')->nullable();
                $table->string('file_path')->nullable(); // added here so no separate ALTER later
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();
            });
        } else {
            // If the table already exists (e.g., on non-fresh DB), ensure column exists
            Schema::table('submissions', function (Blueprint $table) {
                if (!Schema::hasColumn('submissions', 'file_path')) {
                    $table->string('file_path')->nullable()->after('content');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
