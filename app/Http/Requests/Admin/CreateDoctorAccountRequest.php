<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateDoctorAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:255', 'unique:users'],
            'gender' => ['required', 'string', 'max:1', 'in:M,F'],
            'date_of_birth' => ['required', 'string', 'max:255', 'date', 'before:today'],
            'profile_picture' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'specialty' => ['required', 'string', 'max:255'],
            'medical_license_number' => ['required', 'string', 'max:255'],
            'medical_school' => ['required', 'string', 'max:255'],
            'year_of_graduation' => ['required', 'string', 'max:255', 'date_format:Y'],
            'biography' => ['required', 'string'],
            'address' => ['required', 'string', 'max:255'],
            'consultation_fee' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function passedValidation()
    {
        $this->merge([
            'password' => Hash::make(Str::password()),
            'accountData' => [
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'profile_picture' => $this->profile_picture,
                'gender' => $this->gender,
                'date_of_birth' => $this->date_of_birth,
                'password' => Hash::make(Str::password()),
            ],
            'profileData' => [
                'specialty' => $this->specialty,
                'medical_license_number' => $this->medical_license_number,
                'medical_school' => $this->medical_school,
                'year_of_graduation' => $this->year_of_graduation,
                'biography' => $this->biography,
                'address' => $this->address,
                'consultation_fee' => $this->consultation_fee,
            ],
        ]);
    }
}
