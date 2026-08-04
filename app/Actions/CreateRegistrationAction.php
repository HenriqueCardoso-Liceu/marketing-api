<?php

namespace App\Actions;

use App\DTOs\CreateRegistrationDTO;
use App\Models\Registration;

class CreateRegistrationAction
{
    public function execute(CreateRegistrationDTO $dto): Registration
    {
        $data = $dto->toArray();

        return Registration::firstOrCreate(
            ['mobile_phone' => $data['mobile_phone']],
            $data
        );
    }
}
