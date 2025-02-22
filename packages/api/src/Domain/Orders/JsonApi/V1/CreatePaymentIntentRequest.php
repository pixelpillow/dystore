<?php

namespace Dystore\Api\Domain\Orders\JsonApi\V1;

use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;

class CreatePaymentIntentRequest extends ResourceRequest
{
    /**
     * Get the validation rules for the resource.
     *
     * @return array<string,array<int,mixed>>
     */
    public function rules(): array
    {
        $paymentTypes = Config::get('lunar.payments.types');

        return [
            'payment_method' => [
                'required',
                'string',
                Rule::in(array_keys($paymentTypes)),
            ],
            'amount' => [
                'nullable',
                'integer',
            ],
            'meta' => [
                'nullable',
                'array',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string,string>
     */
    public function messages(): array
    {
        return [
            'payment_method.required' => __('dystore::validations.payments.payment_method.required'),
            'payment_method.string' => __('dystore::validations.payments.payment_method.string'),
            'payment_method.in' => __(
                'dystore::validations.payments.payment_method.in',
                ['types' => implode(', ', array_keys(Config::get('lunar.payments.types')))],
            ),
            'meta.array' => __('dystore::validations.payments.meta.array'),
        ];
    }
}
