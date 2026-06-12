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
        Schema::table('leads', function (Blueprint $table) {
            $table->string('guardians_name')->nullable()->after('name');
            $table->string('guardians_phone')->nullable()->after('mobile_phone');
            $table->string('reason_of_registration')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['guardians_name', 'guardians_phone', 'reason_of_registration']);
        });
    }
};
