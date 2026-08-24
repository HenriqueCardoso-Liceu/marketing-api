<?php

namespace App\Exceptions;

use RuntimeException;

class DuplicateRegistrationException extends RuntimeException
{
    /**
     * @param  string  $field  Campo que gerou o conflito (mobile_phone|email)
     */
    public function __construct(public readonly string $field, string $message)
    {
        parent::__construct($message);
    }

    public static function mobilePhone(): self
    {
        return new self('mobile_phone', 'Já existe um cadastro com este telefone.');
    }

    public static function email(): self
    {
        return new self('email', 'Já existe um cadastro com este e-mail.');
    }

    /**
     * Formato igual ao de um erro de validação (422), para o front tratar do mesmo jeito.
     */
    public function errors(): array
    {
        return [$this->field => [$this->getMessage()]];
    }
}
