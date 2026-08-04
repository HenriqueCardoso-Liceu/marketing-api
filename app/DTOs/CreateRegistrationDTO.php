<?php

namespace App\DTOs;

use Illuminate\Support\Str;

class CreateRegistrationDTO
{
    public function __construct(
        public readonly ?string $student_name,
        public readonly ?string $responsible_name,
        public readonly string $mobile_phone,
        public readonly ?string $email,
        public readonly ?string $date_of_birth,
        public readonly ?string $street,
        public readonly ?string $number,
        public readonly ?string $neighborhood,
        public readonly ?string $postal_code,
        public readonly ?string $city,
        public readonly ?string $state,
        public readonly ?string $education_level,
        public readonly ?string $current_school,
        public readonly string $lead_source,

        public readonly ?string $utm_source = null,
        public readonly ?string $utm_medium = null,
        public readonly ?string $utm_campaign = null,
        public readonly ?string $utm_term = null,
        public readonly ?string $utm_content = null,
        public readonly ?string $gclid = null,
        public readonly ?string $fbclid = null,
        public readonly ?string $msclkid = null,
        public readonly ?string $referrer = null,
        public readonly ?string $landing_page = null,
    ) {
    }


    public static function fromArray(array $data): self
    {
        $required = [
            'student_name',
            'responsible_name',
            'mobile_phone',
            'email',
            'date_of_birth',
            'street',
            'number',
            'neighborhood',
            'postal_code',
            'city',
            'state',
            'education_level',
            'current_school',
            'lead_source',
        ];

        foreach ($required as $field) {
            if (!array_key_exists($field, $data)) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }

        $dob = self::toYmdDate($data['date_of_birth'] ?? null);
        $state = strtoupper(trim((string) $data['state']));
        $currentSchool = (string) $data['current_school'];
        if (method_exists(Str::class, 'hasMacro') && Str::hasMacro('ptBrTitle')) {
            /** @phpstan-ignore-next-line */
            $currentSchool = Str::ptBrTitle($currentSchool);
        }

        return new self(
            student_name: self::nullIfEmpty($data['student_name'] ?? null),
            responsible_name: self::nullIfEmpty($data['responsible_name'] ?? null),
            mobile_phone: self::nullIfEmpty(self::cleanPhoneMask((string) ($data['mobile_phone'] ?? ''))),
            email: self::nullIfEmpty(strtolower((string) ($data['email'] ?? ''))),
            date_of_birth: $dob,
            street: self::nullIfEmpty($data['street'] ?? null),
            number: self::nullIfEmpty($data['number'] ?? null),
            neighborhood: self::nullIfEmpty($data['neighborhood'] ?? null),
            postal_code: self::nullIfEmpty(self::cleanPostalCodeMask((string) ($data['postal_code'] ?? ''))),
            city: self::nullIfEmpty($data['city'] ?? null),
            state: self::nullIfEmpty(isset($data['state']) ? strtoupper(trim((string) $data['state'])) : null),
            education_level: self::nullIfEmpty($data['education_level'] ?? null),
            current_school: self::nullIfEmpty($currentSchool),
            lead_source: self::nullIfEmpty((string) ($data['lead_source'] ?? 'direct')) ?? 'direct',

            utm_source: self::nullIfEmpty($data['utm_source'] ?? null),
            utm_medium: self::nullIfEmpty($data['utm_medium'] ?? null),
            utm_campaign: self::nullIfEmpty($data['utm_campaign'] ?? null),
            utm_term: self::nullIfEmpty($data['utm_term'] ?? null),
            utm_content: self::nullIfEmpty($data['utm_content'] ?? null),
            gclid: self::nullIfEmpty($data['gclid'] ?? null),
            fbclid: self::nullIfEmpty($data['fbclid'] ?? null),
            msclkid: self::nullIfEmpty($data['msclkid'] ?? null),
            referrer: self::nullIfEmpty($data['referrer'] ?? null),
            landing_page: self::nullIfEmpty($data['landing_page'] ?? null),
        );

    }

    private static function nullIfEmpty(?string $v): ?string
    {
        if ($v === null)
            return null;
        $t = trim($v);
        return $t === '' ? null : $t;
    }

    /**
     * Converte "dd/mm/yyyy" em "Y-m-d". Se já vier "Y-m-d", mantém.
     */
    private static function toYmdDate(?string $date): ?string
    {
        if ($date === null) {
            return null;
        }

        $date = trim($date);

        if ($date === '') {
            return null; // ponto chave: nunca devolve ""
        }

        // já no formato correto
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        // formato brasileiro dd/mm/yyyy
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
            [$d, $m, $y] = explode('/', $date);
            return sprintf('%04d-%02d-%02d', (int) $y, (int) $m, (int) $d);
        }

        // formato inválido → melhor devolver null para não quebrar o banco
        return null;
    }


    /**
     * Remove máscara do telefone
     */
    private static function cleanPhoneMask(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone);
    }

    /**
     * Remove máscara do CEP
     */
    private static function cleanPostalCodeMask(string $postalCode): string
    {
        return preg_replace('/\D+/', '', $postalCode);
    }

    public function toArray(): array
    {
        $base = [
            'student_name' => $this->student_name,
            'responsible_name' => $this->responsible_name,
            'mobile_phone' => $this->mobile_phone,
            'email' => $this->email,
            'date_of_birth' => $this->date_of_birth,
            'street' => $this->street,
            'number' => $this->number,
            'neighborhood' => $this->neighborhood,
            'postal_code' => $this->postal_code,
            'city' => $this->city,
            'state' => $this->state,
            'education_level' => $this->education_level,
            'current_school' => $this->current_school,
            'lead_source' => $this->lead_source,

            'utm_source' => $this->utm_source,
            'utm_medium' => $this->utm_medium,
            'utm_campaign' => $this->utm_campaign,
            'utm_term' => $this->utm_term,
            'utm_content' => $this->utm_content,
            'gclid' => $this->gclid,
            'fbclid' => $this->fbclid,
            'msclkid' => $this->msclkid,
            'referrer' => $this->referrer,
            'landing_page' => $this->landing_page,
        ];

        return array_filter($base, static fn($v) => !is_null($v));
    }
}
