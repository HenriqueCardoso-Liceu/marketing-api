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
        Schema::create('matriculas2027-col-itaqua', function (Blueprint $table) {
            $table->string('responsible_name', 150)->nullable();
            $table->string('mobile_phone', 25)->nullable();
            $table->string('interest', 150)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('matriculas2027-col-itaqua');
    }
};
