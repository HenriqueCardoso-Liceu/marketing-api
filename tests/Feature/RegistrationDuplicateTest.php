<?php

use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function registrationPayload(array $overrides = []): array
{
    return array_merge([
        'student_name' => 'Maria Silva',
        'responsible_name' => 'Joana Silva',
        'mobile_phone' => '11988887777',
        'email' => 'maria.silva@gmail.com',
    ], $overrides);
}

it('cria uma registration quando telefone e e-mail sao ineditos', function () {
    $this->postJson('/api/registrations', registrationPayload())
        ->assertStatus(201);

    expect(Registration::count())->toBe(1);
});

it('bloqueia registration com telefone ja existente', function () {
    $this->postJson('/api/registrations', registrationPayload())->assertStatus(201);

    $this->postJson('/api/registrations', registrationPayload([
        'email' => 'outro.email@gmail.com',
    ]))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['mobile_phone']);

    expect(Registration::count())->toBe(1);
});

it('bloqueia registration com e-mail ja existente', function () {
    $this->postJson('/api/registrations', registrationPayload())->assertStatus(201);

    $this->postJson('/api/registrations', registrationPayload([
        'mobile_phone' => '11977776666',
    ]))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);

    expect(Registration::count())->toBe(1);
});

it('compara o telefone ja normalizado, ignorando a mascara', function () {
    $this->postJson('/api/registrations', registrationPayload())->assertStatus(201);

    $this->postJson('/api/registrations', registrationPayload([
        'mobile_phone' => '(11) 98888-7777',
        'email' => 'outro.email@gmail.com',
    ]))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['mobile_phone']);
});

it('permite varias registrations sem e-mail', function () {
    $this->postJson('/api/registrations', registrationPayload(['email' => null]))
        ->assertStatus(201);

    $this->postJson('/api/registrations', registrationPayload([
        'mobile_phone' => '11977776666',
        'email' => null,
    ]))->assertStatus(201);

    expect(Registration::count())->toBe(2);
});

it('mantem os duplicados que ja existem no banco', function () {
    // Duplicados legados, gravados direto no banco (sem passar pela API).
    Registration::create(registrationPayload());
    Registration::create(registrationPayload());

    expect(Registration::count())->toBe(2);

    // A partir de agora, uma nova tentativa pela API e barrada.
    $this->postJson('/api/registrations', registrationPayload())
        ->assertStatus(422)
        ->assertJsonValidationErrors(['mobile_phone', 'email']);

    expect(Registration::count())->toBe(2);
});
