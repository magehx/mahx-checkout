<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data;

use Rkt\MageData\Data;

class PaymentMethodData extends Data
{
    public function __construct(
        public string $code,
        public ?string $title = '',
        public ?array $additionalData = [],
    ) {
    }

    public function rules(): array
    {
        return [
            'code' => 'required',
        ];
    }
}
