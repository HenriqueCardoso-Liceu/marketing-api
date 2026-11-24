<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Captura do referral de click-to-WhatsApp (CTWA): uma linha por mensagem inicial
 * que trouxe referral de anúncio. Chave de join com os leads: phone_last8
 * (últimos 8 dígitos), igual ao nphone() do leads_tracking_sync.
 *
 * Diferente do schema.sql original, não guarda o payload cru da mensagem
 * (continha o texto digitado pelo lead) — só o necessário para a atribuição.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ctwa_capture', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 24);                       // wa_id completo (ex.: 5511999990000)
            $table->char('phone_last8', 8)->index();           // chave de join com os leads
            $table->string('ctwa_clid', 600)->nullable();      // id do clique (necessário p/ CAPI)
            $table->string('ad_id', 64)->nullable()->index();  // referral.source_id
            $table->string('source_type', 32)->nullable();     // 'ad' | 'post'
            $table->text('source_url')->nullable();
            $table->text('headline')->nullable();              // texto do anúncio, não do lead
            $table->text('body')->nullable();
            $table->string('waba_id', 32)->nullable();
            $table->string('phone_number_id', 32)->nullable();
            $table->string('wamid', 160)->unique();            // id da mensagem (idempotência)
            $table->dateTime('msg_ts')->nullable();            // UTC
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ctwa_capture');
    }
};
