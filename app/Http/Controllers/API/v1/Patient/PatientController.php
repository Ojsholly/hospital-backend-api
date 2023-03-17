<?php

namespace App\Http\Controllers\API\v1\Patient;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\RegistrationRequest;
use App\Http\Resources\User\UserResource;
use App\Services\Auth\AuthService;
use App\Traits\FileUploads;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class PatientController extends Controller
{
    use FileUploads;

    public function __construct(private readonly AuthService $authService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RegistrationRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['profile_picture'] = $this->uploadFile($request->file('profile_picture'), 'profile_pictures');

            $user = $this->authService->createAccount($data, RoleEnum::PATIENT);
        } catch (Throwable $exception) {
            report($exception);

            return response()->internalServerError('An error occurred while creating the patient account.');
        }

        return response()->created(new UserResource($user), 'Patient account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
