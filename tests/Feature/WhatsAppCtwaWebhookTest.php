<?php

use App\Models\CtwaCapture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.ctwa.app_secret' => 'segredo-ctwa',
        'services.ctwa.verify_token' => 'token-ctwa',
    ]);
});

function ctwaPayload(array $messages, string $field = 'messages'): array
{
    return [
        'object' => 'whatsapp_business_account',
        'entry' => [[
            'id' => '816557877464635',
            'changes' => [[
                'field' => $field,
                'value' => [
                    'messaging_product' => 'whatsapp',
                    'metadata' => ['phone_number_id' => '111222333'],
                    'messages' => $messages,
                ],
            ]],
        ]],
    ];
}

function ctwaMessage(string $wamid = 'wamid.ABC', array $overrides = []): array
{
    return array_merge([
        'from' => '5511976534479',
        'id' => $wamid,
        'timestamp' => '1764000000',
        'type' => 'text',
        'text' => ['body' => 'Olá, quero saber das matrículas'],
        'referral' => [
            'source_url' => 'https://fb.me/abc',
            'source_id' => '120200000000000001',
            'source_type' => 'ad',
            'headline' => 'Matrículas 2027',
            'body' => 'Garanta a vaga',
            'ctwa_clid' => 'ARAkLkA8rmlFeiCktEJQ',
        ],
    ], $overrides);
}

function postCtwa($test, array $payload, ?string $secret = 'segredo-ctwa')
{
    $raw = json_encode($payload);
    $headers = ['CONTENT_TYPE' => 'application/json'];

    if ($secret !== null) {
        $headers['HTTP_X_HUB_SIGNATURE_256'] = 'sha256=' . hash_hmac('sha256', $raw, $secret);
    }

    return $test->call('POST', '/api/webhooks/meta/whatsapp', [], [], [], $headers, $raw);
}

// ── Verificação (GET) ──────────────────────────────────────────────

it('responde o challenge quando o verify token bate', function () {
    $this->get('/api/webhooks/meta/whatsapp?hub.mode=subscribe&hub.verify_token=token-ctwa&hub.challenge=12345')
        ->assertOk()
        ->assertSeeText('12345');
});

it('recusa a verificação com token errado ou sem token configurado', function () {
    $this->get('/api/webhooks/meta/whatsapp?hub.mode=subscribe&hub.verify_token=errado&hub.challenge=1')
        ->assertStatus(403);

    config(['services.ctwa.verify_token' => null]);

    $this->get('/api/webhooks/meta/whatsapp?hub.mode=subscribe&hub.verify_token=&hub.challenge=1')
        ->assertStatus(403);
});

it('nao aceita o verify token do webhook de Lead Ads', function () {
    config(['services.meta.verify_token' => 'token-lead-ads']);

    $this->get('/api/webhooks/meta/whatsapp?hub.mode=subscribe&hub.verify_token=token-lead-ads&hub.challenge=1')
        ->assertStatus(403);
});

// ── Assinatura ─────────────────────────────────────────────────────

it('recusa POST sem assinatura ou com assinatura errada', function () {
    postCtwa($this, ctwaPayload([ctwaMessage()]), null)->assertStatus(403);
    postCtwa($this, ctwaPayload([ctwaMessage()]), 'outro-segredo')->assertStatus(403);

    expect(CtwaCapture::count())->toBe(0);
});

it('recusa tudo quando CTWA_APP_SECRET nao esta configurado', function () {
    config(['services.ctwa.app_secret' => null]);

    postCtwa($this, ctwaPayload([ctwaMessage()]), '')->assertStatus(403);

    expect(CtwaCapture::count())->toBe(0);
});

// ── Gravação ───────────────────────────────────────────────────────

it('grava o referral da primeira mensagem vinda de anuncio', function () {
    postCtwa($this, ctwaPayload([ctwaMessage()]))->assertOk();

    $row = CtwaCapture::firstOrFail();

    expect($row->phone)->toBe('5511976534479')
        ->and($row->phone_last8)->toBe('76534479')
        ->and($row->ad_id)->toBe('120200000000000001')
        ->and($row->ctwa_clid)->toBe('ARAkLkA8rmlFeiCktEJQ')
        ->and($row->source_type)->toBe('ad')
        ->and($row->waba_id)->toBe('816557877464635')
        ->and($row->phone_number_id)->toBe('111222333')
        ->and($row->wamid)->toBe('wamid.ABC')
        ->and($row->msg_ts->format('Y-m-d H:i:s'))->toBe('2025-11-24 16:00:00');
});

it('nao guarda o texto digitado pelo lead', function () {
    expect(Schema::hasColumn('ctwa_capture', 'raw'))->toBeFalse();

    postCtwa($this, ctwaPayload([ctwaMessage()]))->assertOk();

    expect(json_encode(CtwaCapture::firstOrFail()->toArray()))
        ->not->toContain('quero saber das matrículas');
});

it('ignora mensagens sem referral, status e outros campos', function () {
    $semReferral = ctwaMessage('wamid.SEM');
    unset($semReferral['referral']);

    postCtwa($this, ctwaPayload([$semReferral]))->assertOk();
    postCtwa($this, ctwaPayload([ctwaMessage('wamid.OUTRO')], 'statuses'))->assertOk();
    postCtwa($this, ['entry' => [['id' => '1', 'changes' => [['field' => 'messages', 'value' => ['statuses' => [['id' => 'x']]]]]]]])
        ->assertOk();

    expect(CtwaCapture::count())->toBe(0);
});

it('e idempotente por wamid quando o Meta reenvia', function () {
    postCtwa($this, ctwaPayload([ctwaMessage()]))->assertOk();
    postCtwa($this, ctwaPayload([ctwaMessage()]))->assertOk();

    expect(CtwaCapture::count())->toBe(1);
});

it('responde 200 para payload que nao e JSON', function () {
    $raw = 'nao-e-json';
    $sig = 'sha256=' . hash_hmac('sha256', $raw, 'segredo-ctwa');

    $this->call('POST', '/api/webhooks/meta/whatsapp', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_HUB_SIGNATURE_256' => $sig,
    ], $raw)->assertOk();
});

it('responde 500 quando o banco falha, para o Meta reenviar', function () {
    Schema::drop('ctwa_capture');

    postCtwa($this, ctwaPayload([ctwaMessage()]))->assertStatus(500);
});

// ── Rate limit ─────────────────────────────────────────────────────

it('usa limiter proprio, separado do webhook de Lead Ads', function () {
    config([
        'security.rate_limits.webhooks_per_minute' => 1,
        'security.rate_limits.whatsapp_webhooks_per_minute' => 2,
    ]);

    // Esgota o limiter do Lead Ads...
    $this->get('/api/webhooks/meta/leads?hub.mode=subscribe');
    $this->get('/api/webhooks/meta/leads?hub.mode=subscribe')->assertStatus(429);

    // ...e o do WhatsApp continua com a própria cota.
    postCtwa($this, ctwaPayload([ctwaMessage('wamid.1')]))->assertOk();
    postCtwa($this, ctwaPayload([ctwaMessage('wamid.2')]))->assertOk();
    postCtwa($this, ctwaPayload([ctwaMessage('wamid.3')]))->assertStatus(429);
});
