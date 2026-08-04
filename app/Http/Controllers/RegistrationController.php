<?php

namespace App\Http\Controllers;

use App\Actions\CreateRegistrationAction;
use App\DTOs\CreateRegistrationDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegistrationRequest;
use App\Http\Resources\RegistrationResource;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'exception' => $e->getMessage()
            ], 500);
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
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'exception' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Registration $registration): JsonResponse
    {

        $registration->update($request->all());

        try {
            return response()->json([
                'success' => true,
                'data' => RegistrationResource::make($registration)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'exception' => $e->getMessage()
            ], 500);
        }
    }
}
