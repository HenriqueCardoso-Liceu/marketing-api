<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class Registration extends Model
{

    use HasUlids;

    protected $fillable = [
        'student_name',
        'responsible_name',
        'mobile_phone',
        'email',
        'date_of_birth',
        'street',
        'number',
        'neighborhood',
        'complement',
        'postal_code',
        'city',
        'state',
        'education_level',
        'current_school',
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

    protected $casts = [
        'date_of_birth' => 'date:Y-m-d',
    ];

    protected function mobilePhone(): Attribute
    {
        return Attribute::make(
            set: fn($v) => preg_replace('/\D+/', '', (string) $v),
        );
    }

    protected function postalCode(): Attribute
    {
        return Attribute::make(
            set: fn($v) => preg_replace('/\D+/', '', (string) $v),
        );
    }

    protected function state(): Attribute
    {
        return Attribute::make(
            set: fn($v) => $v ? strtoupper(trim((string) $v)) : null,
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn($v) => $v ? strtolower(trim((string) $v)) : null,
        );
    }

    protected function currentSchool(): Attribute
    {
        return Attribute::make(
            set: function ($v) {
                $v = is_string($v) ? trim($v) : $v;
                if (method_exists(Str::class, 'hasMacro') && Str::hasMacro('ptBrTitle')) {
                    /** @phpstan-ignore-next-line */
                    return Str::ptBrTitle((string) $v);
                }
                return $v;
            }
        );
    }

    public function getMobilePhoneFormattedAttribute(): ?string
    {
        $v = $this->mobile_phone;
        if (!$v)
            return null;
        if (preg_match('/^(\d{2})(\d{5})(\d{4})$/', $v, $m))
            return "($m[1]) $m[2]-$m[3]";
        if (preg_match('/^(\d{2})(\d{4})(\d{4})$/', $v, $m))
            return "($m[1]) $m[2]-$m[3]";
        return $v;
    }

    public function getPostalCodeFormattedAttribute(): ?string
    {
        $v = $this->postal_code;
        return $v && preg_match('/^(\d{5})(\d{3})$/', $v, $m) ? "$m[1]-$m[2]" : $v;
    }
}
