<?php

namespace App\Http\Controllers;

use App\Actions\CreateRegistrationAction;
use App\DTOs\CreateRegistrationDTO;
use App\Exceptions\DuplicateRegistrationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegistrationRequest;
use App\Http\Requests\UpdateRegistrationRequest;
use App\Http\Resources\RegistrationResource;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class RegistrationController extends Controller
{
    public function store(StoreRegistrationRequest $request): JsonResponse
    {
        try {
            $dto = CreateRegistrationDTO::fromArray($request->validated());

            $action = new CreateRegistrationAction();
            $registration = $action->execute($dto);

            return response()->json([
                'success' => true,
                'data' => RegistrationResource::make($registration)
            ], 201);
        } catch (DuplicateRegistrationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 409);
        } catch (\Exception $e) {
            return $this->unexpectedError($e);
        }
    }

    public function show(Registration $registration): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => RegistrationResource::make($registration)
            ], 201);
        } catch (\Exception $e) {
            return $this->unexpectedError($e);
        }
    }

    public function update(UpdateRegistrationRequest $request, Registration $registration): JsonResponse
    {
        try {
            $registration->update($request->validated());

            return response()->json([
                'success' => true,
                'data' => RegistrationResource::make($registration)
            ], 201);
        } catch (\Exception $e) {
            return $this->unexpectedError($e);
        }
    }

    /**
     * Detalhes da exceção ficam só no log; o cliente recebe mensagem genérica.
     */
    private function unexpectedError(\Throwable $e): JsonResponse
    {
        Log::error('RegistrationController: erro inesperado', ['exception' => $e]);

        return response()->json([
            'success' => false,
            'message' => 'An unexpected error occurred',
        ], 500);
    }
}
