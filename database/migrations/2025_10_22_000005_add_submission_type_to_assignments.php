<?php
// database/migrations/2025_10_22_000001_add_submission_type_to_assignments.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('assignments', function (Blueprint $table) {
            // 'text', 'pdf', 'both'  (default to 'both' for backward compatibility)
            $table->string('submission_type')->default('both')->after('deadline');
        });
    }
    public function down(): void {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn('submission_type');
        });
    }
};
