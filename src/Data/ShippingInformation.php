<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data;

use Rkt\MageData\Data;

class ShippingInformation extends Data
{
    public function __construct(
        public AddressData $address,
        public string $method,
        public ?int $customer_id = null,
    ) {
    }

    public function rules(): array
    {
        return [
            'method' => 'required|valid_shipping_method_code',
        ];
    }

    public function customRules(): array
    {
        return [
            'valid_shipping_method_code' => fn ($value) => count(array_filter(explode('_', $value))) === 2,
        ];
    }

    public function getAddress(): array
    {
        return $this->address->toArray();
    }

    public function getShippingMethodCode(): string
    {
        return explode('_', $this->method)[1] ?? '';
    }

    public function getShippingCarrierCode(): string
    {
        return explode('_', $this->method)[0] ?? '';
    }
}
