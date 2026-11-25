<?php

use App\Models\leads;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.internal_api.key' => 'chave-de-teste',
        'services.internal_api.enforce' => true,
        'services.meta.app_secret' => 'segredo-de-teste',
        'services.meta.verify_token' => 'token-de-teste',
    ]);
});

// ── POST /api/leads ────────────────────────────────────────────────

it('nao devolve dados do lead nem sobrescreve um lead existente', function () {
    leads::create([
        'name' => 'Maria Original',
        'email' => 'maria@example.com',
        'mobile_phone' => '11988887777',
    ]);

    $response = $this->postJson('/api/leads', [
        'name' => 'Atacante',
        'email' => 'maria@example.com',
        'mobile_phone' => '11900000000',
        'city' => 'Suzano',
    ]);

    $response->assertStatus(201)
        ->assertExactJson(['message' => 'Lead received successfully']);

    $lead = leads::where('email', 'maria@example.com')->first();
    expect($lead->name)->toBe('Maria Original')
        ->and($lead->mobile_phone)->toBe('11988887777')
        ->and($lead->city)->toBe('Suzano'); // campo vazio é completado
});

it('responde igual para lead novo e existente', function () {
    $novo = $this->postJson('/api/leads', ['name' => 'A', 'email' => 'a@example.com']);
    $repetido = $this->postJson('/api/leads', ['name' => 'B', 'email' => 'a@example.com']);

    expect($novo->status())->toBe($repetido->status())
        ->and($novo->json())->toBe($repetido->json());
});

it('aceita lead sem e-mail quando ha celular', function () {
    $this->postJson('/api/leads', ['name' => 'Sem Email', 'mobile_phone' => '11977776666'])
        ->assertStatus(201);

    expect(leads::where('mobile_phone', '11977776666')->first()->email)->toBeNull();
});

it('deduplica lead sem e-mail pelo celular sem sobrescrever', function () {
    $this->postJson('/api/leads', ['name' => 'Original', 'mobile_phone' => '11977776666'])->assertStatus(201);
    $this->postJson('/api/leads', ['name' => 'Outro', 'mobile_phone' => '11977776666', 'city' => 'Suzano'])
        ->assertStatus(201);

    $leads = leads::where('mobile_phone', '11977776666')->get();
    expect($leads)->toHaveCount(1)
        ->and($leads->first()->name)->toBe('Original')
        ->and($leads->first()->city)->toBe('Suzano');
});

it('exige e-mail ou celular no lead', function () {
    $this->postJson('/api/leads', ['name' => 'Sem Contato'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

// ── Rotas internas ─────────────────────────────────────────────────

it('bloqueia GET /api/leads sem chave', function () {
    $this->getJson('/api/leads')->assertStatus(401);
});

it('bloqueia GET /api/leads com chave errada', function () {
    $this->getJson('/api/leads', ['X-Api-Key' => 'errada'])->assertStatus(401);
});

it('libera GET /api/leads com a chave por header ou Bearer', function () {
    $this->getJson('/api/leads', ['X-Api-Key' => 'chave-de-teste'])->assertOk();
    $this->getJson('/api/leads', ['Authorization' => 'Bearer chave-de-teste'])->assertOk();
});

it('bloqueia tudo quando a chave nao esta configurada', function () {
    config(['services.internal_api.key' => null]);

    $this->getJson('/api/leads', ['X-Api-Key' => ''])->assertStatus(401);
});

// ── Rate limit ─────────────────────────────────────────────────────

it('aplica rate limit nos formularios publicos', function () {
    config(['security.rate_limits.public_forms_per_minute' => 3]);

    foreach (range(1, 3) as $i) {
        $this->postJson('/api/col-itaqua/registrations', [
            'responsible_name' => "Resp {$i}",
            'mobile_phone' => '11988887777',
            'interest' => '1º ano',
        ])->assertStatus(201);
    }

    $this->postJson('/api/col-itaqua/registrations', [
        'responsible_name' => 'Resp 4',
        'mobile_phone' => '11988887777',
        'interest' => '1º ano',
    ])->assertStatus(429);
});

// ── /api/registrations/{id} ────────────────────────────────────────

it('nao expoe cadastro fora da janela de edicao', function () {
    $registration = Registration::create([
        'responsible_name' => 'Joana Silva',
        'mobile_phone' => '11988887777',
    ]);
    $registration->forceFill(['created_at' => now()->subHours(3)])->save();

    $this->getJson("/api/registrations/{$registration->id}")->assertStatus(404);
    $this->patchJson("/api/registrations/{$registration->id}", ['student_name' => 'X'])->assertStatus(404);

    expect($registration->fresh()->student_name)->toBeNull();
});

it('permite ler o cadastro dentro da janela de edicao', function () {
    $registration = Registration::create([
        'responsible_name' => 'Joana Silva',
        'mobile_phone' => '11988887777',
    ]);

    $this->getJson("/api/registrations/{$registration->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.responsible_first_name', 'Joana');
});

// ── Webhook da Meta ────────────────────────────────────────────────

it('recusa webhook sem assinatura ou com assinatura invalida', function () {
    $this->postJson('/api/webhooks/meta/leads', ['entry' => []])->assertStatus(403);
    $this->postJson('/api/webhooks/meta/leads', ['entry' => []], ['X-Hub-Signature-256' => 'sha256=abc'])
        ->assertStatus(403);
});

it('aceita webhook com assinatura valida', function () {
    $body = json_encode(['entry' => []]);
    $signature = 'sha256=' . hash_hmac('sha256', $body, 'segredo-de-teste');

    $this->call('POST', '/api/webhooks/meta/leads', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_HUB_SIGNATURE_256' => $signature,
    ], $body)->assertOk();
});

it('recusa webhook quando META_APP_SECRET nao esta configurado', function () {
    config(['services.meta.app_secret' => null]);

    $body = json_encode(['entry' => []]);
    $signature = 'sha256=' . hash_hmac('sha256', $body, '');

    $this->call('POST', '/api/webhooks/meta/leads', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_HUB_SIGNATURE_256' => $signature,
    ], $body)->assertStatus(403);
});

it('recusa verificacao do webhook sem token configurado', function () {
    config(['services.meta.verify_token' => null]);

    $this->get('/api/webhooks/meta/leads?hub_mode=subscribe&hub_challenge=123')->assertStatus(403);
});

it('aceita verificacao do webhook com token correto', function () {
    $this->get('/api/webhooks/meta/leads?hub_mode=subscribe&hub_verify_token=token-de-teste&hub_challenge=123')
        ->assertOk()
        ->assertSee('123');
});
