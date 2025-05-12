<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data\PaymentInformation;

use Rkt\MageData\Data;

class PaymentMethod extends Data
{
    public function __construct(
        public string $method,
        public ?array $additionalData = [],
    ) {
    }

    public function rules(): array
    {
        return [
            'method' => 'required',
        ];
    }
}
