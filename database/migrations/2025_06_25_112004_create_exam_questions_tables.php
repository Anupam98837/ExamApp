<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /* ───────── exam_questions ───────── */
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();                                     // question_id
            $table->unsignedBigInteger('exam_id');            // FK → exams.id

            $table->unsignedInteger('question_order')->default(1);
            $table->longText('question_title');               // Changed to longText for HTML content
            $table->longText('question_description')->nullable(); // Changed to longText
            $table->longText('answer_explanation')->nullable();  // Changed to longText

            $table->enum('question_type', ['mcq','true_false','fill_in_the_blank']);
            $table->unsignedInteger('question_mark')->default(1);
            $table->json('question_settings')->nullable();

            $table->timestamps();

            $table->foreign('exam_id')
                  ->references('id')->on('exams')
                  ->cascadeOnDelete();
        });

        /* ───────── exam_question_answers ───────── */
        Schema::create('exam_question_answers', function (Blueprint $table) {
            $table->id();                                     // answer_id
            $table->unsignedBigInteger('belongs_question_id');// FK → exam_questions.id

            /* optional metadata */
            $table->string('belongs_question_type')->nullable();

            /* answer fields */
            $table->longText('answer_title')->nullable();      // Changed to longText for HTML content
            $table->boolean('is_correct')->default(false);

            $table->unsignedBigInteger('image_id')->nullable();
            $table->string('answer_two_gap_match')->nullable();  // for gap-fill
            $table->string('answer_view_format')->nullable();
            $table->json('answer_settings')->nullable();

            $table->unsignedInteger('answer_order')->default(0);
            $table->timestamps();

            $table->foreign('belongs_question_id')
                  ->references('id')->on('exam_questions')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_question_answers');
        Schema::dropIfExists('exam_questions');
    }
};