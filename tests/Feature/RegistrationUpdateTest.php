<?php

use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function existingRegistration(array $overrides = []): Registration
{
    return Registration::create(array_merge([
        'student_name' => 'Maria Silva',
        'responsible_name' => 'Joana Silva',
        'mobile_phone' => '11988887777',
        'email' => 'maria.silva@gmail.com',
    ], $overrides));
}

it('atualiza campos comuns normalmente', function () {
    $registration = existingRegistration();

    $this->patchJson("/api/registrations/{$registration->id}", [
        'student_name' => 'Maria Silva Souza',
    ])->assertStatus(201);

    expect($registration->fresh()->student_name)->toBe('Maria Silva Souza');
});

it('bloqueia update que rouba o telefone de outro cadastro', function () {
    $alvo = existingRegistration();
    existingRegistration([
        'mobile_phone' => '11977776666',
        'email' => 'outro@gmail.com',
    ]);

    $this->patchJson("/api/registrations/{$alvo->id}", [
        'mobile_phone' => '11977776666',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['mobile_phone']);

    expect($alvo->fresh()->mobile_phone)->toBe('11988887777');
});

it('bloqueia update que rouba o e-mail de outro cadastro', function () {
    $alvo = existingRegistration();
    existingRegistration([
        'mobile_phone' => '11977776666',
        'email' => 'outro@gmail.com',
    ]);

    $this->patchJson("/api/registrations/{$alvo->id}", [
        'email' => 'outro@gmail.com',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);

    expect($alvo->fresh()->email)->toBe('maria.silva@gmail.com');
});

it('permite reenviar os proprios telefone e e-mail sem conflito', function () {
    $registration = existingRegistration();

    $this->patchJson("/api/registrations/{$registration->id}", [
        'mobile_phone' => '(11) 98888-7777',
        'email' => 'MARIA.SILVA@gmail.com',
        'student_name' => 'Maria S. Silva',
    ])->assertStatus(201);

    expect($registration->fresh()->student_name)->toBe('Maria S. Silva');
});

it('nao apaga campos ausentes do corpo do patch', function () {
    $registration = existingRegistration([
        'city' => 'Sao Paulo',
        'state' => 'SP',
    ]);

    $this->patchJson("/api/registrations/{$registration->id}", [
        'student_name' => 'Maria Silva Souza',
    ])->assertStatus(201);

    $fresh = $registration->fresh();
    expect($fresh->city)->toBe('Sao Paulo')
        ->and($fresh->state)->toBe('SP')
        ->and($fresh->email)->toBe('maria.silva@gmail.com');
});

it('rejeita telefone em formato invalido no update', function () {
    $registration = existingRegistration();

    $this->patchJson("/api/registrations/{$registration->id}", [
        'mobile_phone' => '123',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['mobile_phone']);
});

it('ignora campos que nao estao nas regras', function () {
    $registration = existingRegistration();

    $this->patchJson("/api/registrations/{$registration->id}", [
        'id' => 'hackeado',
        'student_name' => 'Maria Silva Souza',
    ])->assertStatus(201);

    expect($registration->fresh()->id)->toBe($registration->id);
});
