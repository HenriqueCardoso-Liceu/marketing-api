<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('student_name', 150)->nullable();
            $table->string('responsible_name', 150)->nullable();
            $table->string('mobile_phone', 20)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('street', 200)->nullable();
            $table->string('number', 20)->nullable();
            $table->string('neighborhood', 120)->nullable();
            $table->string('complement', 120)->nullable();

            $table->string('postal_code', 15)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('state', 2)->nullable();
            $table->string('education_level', 60)->nullable();
            $table->string('current_school', 150)->nullable();

            $table->string('lead_source')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('gclid')->nullable();
            $table->string('fbclid')->nullable();
            $table->string('msclkid')->nullable();
            $table->string('referrer')->nullable();
            $table->string('landing_page')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('email');
            $table->index('mobile_phone');
            $table->index('postal_code');
            $table->index(['lead_source', 'utm_source']);
            $table->index('utm_campaign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
