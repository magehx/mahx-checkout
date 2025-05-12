<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data;

use Rkt\MageData\Data;
use MageHx\MahxCheckout\Data\PaymentInformation\PaymentMethod;

class PaymentInformation extends Data
{
    public function __construct(
        public PaymentMethod $paymentMethod,
        public ?AddressData  $billingAddress = null,
    ) {
    }

    public function rules(): array
    {
        return [
            'paymentMethod' => 'required',
        ];
    }
}
