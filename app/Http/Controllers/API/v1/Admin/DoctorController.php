<?php

namespace App\Http\Controllers\API\v1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateDoctorAccountRequest;
use App\Http\Resources\Doctor\DoctorResource;
use App\Services\Doctor\DoctorService;
use App\Traits\FileUploads;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class DoctorController extends Controller
{
    use FileUploads;

    public function __construct(private DoctorService $doctorService)
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
    public function store(CreateDoctorAccountRequest $request): JsonResponse
    {
        try {
            $accountData = $request->accountData;
            $accountData['profile_picture'] = $this->uploadFile($request->file('profile_picture'), 'doctors/profile-pictures');
            $accountData['date_of_birth'] = $request->date('date_of_birth')->format('Y-m-d');

            $doctor = $this->doctorService->createDoctorAccount($accountData, $request->profileData);
        } catch (Throwable $exception) {
            report($exception);

            return response()->internalServerError("An error occurred while creating the doctor's account.");
        }

        return response()->created(new DoctorResource($doctor), "The doctor's account was created successfully.");
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
