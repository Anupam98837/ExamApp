<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');         // FK → admin.id

            /* ────────── core ────────── */
            $table->string('examName');
            $table->text('examDescription');
            $table->string('examImg')->nullable();
            $table->text('Instructions')->nullable();

            /* ────────── visibility ────────── */
            $table->enum('is_public', ['yes','no'])->default('no');

            /* ────────── pricing ────────── */
            $table->enum('pricing_model', ['free','paid'])->default('free');
            $table->decimal('regular_price', 10, 2)->nullable();
            $table->decimal('sale_price',    10, 2)->nullable();

            /* ────────── result scheduling ────────── */
            $table->enum('result_set_up_type', ['Immediately','Now','Schedule'])
                  ->default('Immediately');
            $table->timestamp('result_release_date')->nullable();

            /* ────────── NEW quiz-level info ────────── */
            $table->unsignedInteger('totalTime')->nullable();      // minutes
            $table->unsignedInteger('total_attempts')->default(1); // attempts allowed
            $table->unsignedInteger('total_questions')->nullable();

            /* ────────── misc ────────── */
            $table->string('associated_course')->nullable();
            $table->string('associated_department')->nullable();

            $table->timestamps();

            $table->foreign('admin_id')
                  ->references('id')->on('admin')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
