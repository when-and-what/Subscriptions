<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscriptionRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'service_id' => [
                'required',
                'integer',
                Rule::exists('services', 'id')->where('user_id', $this->user()->id),
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'price' => 'nullable|decimal:0,2',
            'billing_cycle' => 'integer',
            'auto_renew' => 'boolean',
            'note' => 'nullable',
            'new_price' => 'nullable|decimal:0,2|required_with:new_price_date',
            'new_price_date' => 'nullable|date|required_with:new_price|after:start_date',
        ];
    }
}
