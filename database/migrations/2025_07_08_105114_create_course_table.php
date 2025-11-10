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
        Schema::create('course', function (Blueprint $table) {
            $table->id();

            // The course title must be unique
            $table->string('title')->unique();

            // A longer description of the course
            $table->text('description');

            // URL or path to an image for the course
            $table->string('image')->nullable();

            // Pricing fields, analogous to your exams table
            $table->decimal('regular_price', 10, 2)->nullable();
            $table->decimal('sale_price',    10, 2)->nullable();

            // Timestamps for created_at and updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course');
    }
};
