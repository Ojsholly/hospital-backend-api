<?php

namespace App\Http\Requests\User;

use App\Enums\AppointmentStatusEnum;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class CreateAppointmentRequest extends FormRequest
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
            'doctor_id' => ['required', 'uuid', 'exists:doctors,id'],
            'datetime' => ['required', 'date_format:Y-m-d H:i:s', 'after:today'],
            'reason' => ['required', 'string'],
            'symptoms' => ['required', 'string'],
            'address' => ['required', 'string', 'max:255'],
            'payment_gateway' => ['required', 'string', 'in:'.config('settings.payment-gateways')],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $params = array_merge(parent::validated($key, $default), [
            'user_id' => $this->user()->id,
            'status' => AppointmentStatusEnum::PENDING,
        ]);

        return Arr::except($params, 'payment_gateway');
    }
}
