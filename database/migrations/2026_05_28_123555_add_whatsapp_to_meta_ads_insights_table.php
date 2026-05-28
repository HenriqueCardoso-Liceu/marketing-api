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
        Schema::table('meta_ads_insights', function (Blueprint $table) {
            $table->unsignedInteger('whatsapp')->default(0)->after('leads');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meta_ads_insights', function (Blueprint $table) {
            $table->dropColumn('whatsapp');
        });
    }
};
