<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('student_nim', 15);
            $table->string('student_name', 150);
            $table->string('academic_year', 25)->default('2025/2026-Genap');
            $table->timestamps();

            $table->unique(['course_id', 'student_nim']); // Unique constraint gabungan
        });
    }

    public function down(): void {
        Schema::dropIfExists('enrollments');
    }
};