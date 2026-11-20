<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordSaleRequest extends FormRequest
{
    /**
     * Authorisation happens in the controller against the stock unit.
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
            'price' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:10000000'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:32'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'sold_on' => ['required', 'date', 'before_or_equal:today', 'after_or_equal:'.now()->subDays(30)->toDateString()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sold_on.before_or_equal' => 'The sale date can\'t be in the future.',
            'sold_on.after_or_equal' => 'Sales older than 30 days need to be recorded by the admin.',
        ];
    }
}
