<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_name' => $this->student_name,
            'responsible_name' => $this->responsible_name,
            'responsible_first_name' => explode(' ', $this->responsible_name)[0],

            'education_level' => $this->education_level,
            'current_school' => $this->current_school,
        ];
    }
}
