<?php

namespace App\Http\Requests;

use App\Enums\ReceiptOutcome;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckInShipmentItemRequest extends FormRequest
{
    /**
     * Authorisation happens in the controller against the shipment.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'outcome' => ['required', Rule::enum(ReceiptOutcome::class)],
            'notes' => [
                Rule::requiredIf(fn () => $this->input('outcome') !== ReceiptOutcome::Received->value),
                'nullable', 'string', 'max:1000',
            ],
            'photos' => ['array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp,heic', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'notes.required' => 'Describe what is wrong with the item.',
        ];
    }
}
