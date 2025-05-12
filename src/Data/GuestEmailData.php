<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data;

use Rkt\MageData\Data;

class GuestEmailData extends Data
{
    public function __construct(
        public string $email,
    ) {
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
        ];
    }

    public function messages(): array
    {
        return [
            'email:required' => __('Email is required'),
            'email:email' => __('Please provide a valid email address'),
        ];
    }
}
