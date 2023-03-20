<?php

namespace App\Http\Requests\Doctor;

use App\Enums\AppointmentStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'diagnosis' => ['required', 'string'],
            'prescription' => ['required', 'string'],
            'comment' => ['required', 'string'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        return array_merge(parent::validated($key, $default), [
            'status' => AppointmentStatusEnum::COMPLETED,
        ]);
    }
}
