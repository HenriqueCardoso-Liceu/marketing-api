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
        Schema::table('matriculas2027-col-itaqua', function (Blueprint $table) {
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('referrer')->nullable();
            $table->string('landing_page')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matriculas2027-col-itaqua', function (Blueprint $table) {
            //
            $table->dropColumn('utm_source');
            $table->dropColumn('utm_medium');
            $table->dropColumn('utm_campaign');
            $table->dropColumn('utm_term');
            $table->dropColumn('utm_content');
            $table->dropColumn('referrer');
            $table->dropColumn('landing_page');
        });
    }
};
