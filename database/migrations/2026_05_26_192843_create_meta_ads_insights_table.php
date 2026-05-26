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
        Schema::create('meta_ads_insights', function (Blueprint $table) {
            $table->id();
            $table->string('account_name')->nullable();
            $table->string('account_id', 30);
            $table->string('campaign_id', 30)->nullable();
            $table->string('campaign_name')->nullable();
            $table->date('date_start');
            $table->decimal('spend', 12, 4)->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('reach')->default(0);
            $table->decimal('ctr', 8, 4)->default(0);
            $table->decimal('cpm', 10, 4)->default(0);
            $table->decimal('cpc', 10, 4)->default(0);
            $table->unsignedInteger('link_clicks')->default(0);
            $table->unsignedInteger('leads')->default(0);
            $table->unsignedInteger('purchases')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            // chave de upsert
            $table->unique(['account_id', 'campaign_id', 'date_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meta_ads_insights');
    }
};
