<?php

namespace App\Actions;

use App\DTOs\CreateRegistrationDTO;
use App\Exceptions\DuplicateRegistrationException;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class CreateRegistrationAction
{
    public function execute(CreateRegistrationDTO $dto): Registration
    {
        $data = $dto->toArray();

        // A validação do FormRequest já barra duplicados, mas dois envios simultâneos
        // (duplo clique no formulário) podem passar por ela ao mesmo tempo. O lock
        // serializa a checagem e garante que só o primeiro grave.
        return DB::transaction(function () use ($data) {
            $this->guardAgainstDuplicate($data);

            return Registration::create($data);
        });
    }

    private function guardAgainstDuplicate(array $data): void
    {
        $email = $data['email'] ?? null;

        $duplicate = Registration::query()
            ->where(function ($q) use ($data, $email) {
                $q->where('mobile_phone', $data['mobile_phone']);

                if ($email !== null) {
                    $q->orWhere('email', $email);
                }
            })
            ->lockForUpdate()
            ->first();

        if ($duplicate === null) {
            return;
        }

        throw $duplicate->mobile_phone === $data['mobile_phone']
            ? DuplicateRegistrationException::mobilePhone()
            : DuplicateRegistrationException::email();
    }
}
