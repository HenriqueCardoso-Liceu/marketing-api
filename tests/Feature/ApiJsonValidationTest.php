<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

// Webhooks de outros sistemas costumam não mandar "Accept: application/json".
// Antes, a validação recusada virava um 302 para "/" sem dizer o motivo.
it('responde 422 em JSON na API mesmo sem o header Accept', function () {
    $response = $this->post('/api/leads', [
        'name' => 'Fulano',
        'email' => 'fulano@example.com',
        'landing_page' => str_repeat('a', 300),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['landing_page']);
});

it('registra no log os campos recusados, sem os valores enviados', function () {
    Log::spy();

    $this->post('/api/leads', [
        'name' => 'Fulano',
        'email' => 'nao-e-email',
        'mobile_phone' => '11999998888',
    ])->assertStatus(422);

    Log::shouldHaveReceived('warning')
        ->withArgs(function (string $message, array $context) {
            return $message === 'API: validação recusada'
                && $context['path'] === 'api/leads'
                && array_keys($context['errors']) === ['email']
                && ! str_contains(json_encode($context), 'nao-e-email');
        })
        ->once();
});

it('continua aceitando um lead valido sem o header Accept', function () {
    $this->post('/api/leads', [
        'name' => 'Fulano',
        'email' => 'fulano@example.com',
    ])->assertStatus(201);
});
