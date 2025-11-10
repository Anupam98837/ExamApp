<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();

            /* FK’s */
            $table->unsignedBigInteger('student_id');        // → students.id
            $table->unsignedBigInteger('exam_id');           // → exams.id

            /* attempt info */
            $table->boolean('publish_to_student')->default(0);
            $table->boolean('completed')->default(1);        // always 1 if you only save after submit
            $table->unsignedInteger('marks_obtained')->default(0);
            $table->unsignedInteger('total_attempts')->default(1);

            $table->json('students_answer');                 // raw answers JSON

            $table->timestamps();

            /* FK constraints */
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('exam_id')->references('id')->on('exams')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};
