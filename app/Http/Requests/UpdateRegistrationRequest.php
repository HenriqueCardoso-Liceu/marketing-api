<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateRegistrationRequest extends FormRequest
{
    /**
     * Campos que só precisam de trim na normalização.
     */
    private const PLAIN_TEXT_FIELDS = [
        'student_name',
        'responsible_name',
        'street',
        'number',
        'neighborhood',
        'complement',
        'city',
        'education_level',
        'lead_source',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'gclid',
        'fbclid',
        'msclkid',
        'referrer',
        'landing_page',
    ];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * PATCH é parcial: todo campo leva "sometimes", então só é validado
     * quando vem no corpo da requisição.
     */
    public function rules(): array
    {
        $current = $this->route('registration');

        return [
            'student_name' => 'sometimes|nullable|string|max:150',
            'responsible_name' => 'sometimes|nullable|string|max:150',

            'mobile_phone' => [
                'sometimes',
                'required',
                'string',
                'regex:/^\d{10,11}$/',
                Rule::unique('registrations', 'mobile_phone')->ignore($current),
            ],

            'email' => [
                'sometimes',
                'nullable',
                'email:rfc,dns',
                'max:150',
                Rule::unique('registrations', 'email')->ignore($current),
            ],

            'date_of_birth' => 'sometimes|nullable|date_format:Y-m-d|before_or_equal:today',

            'street' => 'sometimes|nullable|string|max:200',
            'number' => 'sometimes|nullable|string|max:20',
            'neighborhood' => 'sometimes|nullable|string|max:120',
            'complement' => 'sometimes|nullable|string|max:120',

            'postal_code' => 'sometimes|nullable|string',

            'city' => 'sometimes|nullable|string|max:120',
            'state' => 'sometimes|nullable|string|size:2',

            'education_level' => 'sometimes|nullable|string|max:150',
            'current_school' => 'sometimes|nullable|string|max:150',

            'lead_source' => 'sometimes|nullable|string|max:100',

            'utm_source' => 'sometimes|nullable|string|max:100',
            'utm_medium' => 'sometimes|nullable|string|max:100',
            'utm_campaign' => 'sometimes|nullable|string|max:150',
            'utm_term' => 'sometimes|nullable|string|max:150',
            'utm_content' => 'sometimes|nullable|string|max:150',
            'gclid' => 'sometimes|nullable|string|max:255',
            'fbclid' => 'sometimes|nullable|string|max:255',
            'msclkid' => 'sometimes|nullable|string|max:255',
            'referrer' => 'sometimes|nullable|string|max:255',
            'landing_page' => 'sometimes|nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'mobile_phone.unique' => 'Já existe outro cadastro com este telefone.',
            'email.unique' => 'Já existe outro cadastro com este e-mail.',
        ];
    }

    /**
     * Normaliza apenas as chaves presentes no corpo — mexer nas ausentes
     * apagaria campos que o PATCH não pretendia tocar.
     */
    protected function prepareForValidation(): void
    {
        $digits = fn(?string $v) => $v !== null ? preg_replace('/\D+/', '', (string) $v) : null;
        $trimOrNull = fn($v) => ($v === null) ? null : (trim((string) $v) === '' ? null : trim((string) $v));

        $normalized = [];

        foreach (self::PLAIN_TEXT_FIELDS as $field) {
            if ($this->has($field)) {
                $normalized[$field] = $trimOrNull($this->input($field));
            }
        }

        if ($this->has('mobile_phone')) {
            $normalized['mobile_phone'] = $trimOrNull($digits($this->input('mobile_phone')));
        }

        if ($this->has('postal_code')) {
            $normalized['postal_code'] = $trimOrNull($digits($this->input('postal_code')));
        }

        if ($this->has('email')) {
            $email = $this->input('email');
            $normalized['email'] = $trimOrNull($email !== null ? strtolower((string) $email) : null);
        }

        if ($this->has('state')) {
            $state = $this->input('state');
            $normalized['state'] = $trimOrNull($state !== null ? strtoupper((string) $state) : null);
        }

        if ($this->has('current_school')) {
            $currentSchool = $this->input('current_school');
            if ($currentSchool !== null && method_exists(Str::class, 'hasMacro') && Str::hasMacro('ptBrTitle')) {
                /** @phpstan-ignore-next-line */
                $currentSchool = Str::ptBrTitle((string) $currentSchool);
            }
            $normalized['current_school'] = $trimOrNull($currentSchool);
        }

        if ($this->has('date_of_birth')) {
            $normalized['date_of_birth'] = $this->normalizeDate($this->input('date_of_birth'));
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    /**
     * Aceita "dd/mm/yyyy" e "Y-m-d"; qualquer outro formato segue adiante
     * para a regra date_format reprovar.
     */
    private function normalizeDate(mixed $dob): ?string
    {
        if (!is_string($dob)) {
            return null;
        }

        $dob = trim($dob);

        if ($dob === '') {
            return null;
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dob)) {
            [$d, $m, $y] = explode('/', $dob);
            return sprintf('%04d-%02d-%02d', (int) $y, (int) $m, (int) $d);
        }

        return $dob;
    }
}
