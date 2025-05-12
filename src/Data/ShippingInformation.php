<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data;

use Rkt\MageData\Data;

class ShippingInformation extends Data
{
    public function __construct(
        public string $firstname,
        public string $lastname,
        public array $street,
        public string $city,
        public string $country_id,
        public string $postcode,
        public string $method,
        public ?string $telephone = '',
        public ?string $region = '',
        public ?bool $same_as_billing = true,
        public ?int $save_in_address_book = 0,
        public ?int $customer_address_id = null,
        public ?int $customer_id = null,
    ) {
    }

    public function rules(): array
    {
        return [
            'firstname' => 'required',
            'lastname' => 'required',
            'street.0' => 'required',
            'city' => 'required',
            'country_id' => 'required|max:2',
            'postcode' => 'required',
            'method' => 'required',
        ];
    }

    public function getAddress(): array
    {
        $addressData = [
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'street' => $this->street,
            'city' => $this->city,
            'country_id' => $this->country_id,
            'postcode' => $this->postcode,
            'region' => $this->region,
            // Resets region_id; Otherwise, old region_id will kept kep in the db
            'region_id' => null,
            'telephone' => $this->telephone,
            'same_as_billing' => $this->same_as_billing,
            'save_in_address_book' => $this->save_in_address_book,
            'customer_address_id' => $this->customer_address_id,
            'customer_id' => $this->customer_id,
        ];

        return $addressData;
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
