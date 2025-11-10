<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            // Basic Details
            $table->string('name')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('plain_password')->nullable();

            // Optional Contact / Identity
            $table->string('alternative_phone', 20)->nullable();
            $table->string('alternative_email')->nullable();
            $table->string('whatsapp_no', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('religion', 100)->nullable();
            $table->string('caste', 100)->nullable();
            $table->string('blood_group', 10)->nullable();
            $table->enum('identity_type', ['Aadhar', 'Voter ID', 'PAN'])->nullable();
            $table->string('identity_details', 20)->nullable();

            // Address
            $table->string('city', 100)->nullable();
            $table->string('po', 100)->nullable();
            $table->string('ps', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('pin', 6)->nullable();

            // Educational Details
            $table->string('department', 255)->nullable();
            $table->string('course', 255)->nullable();

            // New tracking & subscription fields
            $table->integer('current_attempt_count')->default(0);
            $table->boolean('is_subscribed')->default(false);
            $table->string('subscription_plan_type')->nullable();
            $table->integer('free_exam_attempts')->nullable();

            // Approval & status
            $table->boolean('is_approved')->default(false);
            $table->enum('status', ['pending', 'active', 'inactive'])->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
