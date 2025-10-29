<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::create('assignments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('section_id')->constrained('sections')->onDelete('cascade');
    $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // teacher who created it
    $table->string('title');
    $table->text('description')->nullable();
    $table->timestamp('deadline')->nullable();
    $table->boolean('allow_file_upload')->default(true);
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
