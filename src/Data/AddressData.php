<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data;

use Rkt\MageData\Data;

class AddressData extends Data
{
    public function __construct(
        public string $firstname,
        public string $lastname,
        public array $street,
        public string $city,
        public string $country_id,
        public string $postcode,
        public ?string $telephone = '',
        public ?string $region = '',
        public ?int $region_id = null,
        public ?string $customer_address_id = null,
        public ?bool $same_as_billing = false,
        public ?int $save_in_address_book = 0,
    ) {
    }

    public function rules(): array
    {
        return [
            'firstname' => 'required|alpha_spaces',
            'lastname' => 'required|alpha_spaces',
            'street.0' => 'required',
            'city' => 'required|alpha_spaces',
            'country_id' => 'required|max:2',
            'postcode' => 'required',
            'telephone' => 'required',
        ];
    }

    public function aliases(): array
    {
        return [
            'street.0' => __('street'),
        ];
    }

    public function getAddress(): array
    {
        return [
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'street' => $this->street,
            'city' => $this->city,
            'country_id' => $this->country_id,
            'postcode' => $this->postcode,
            'region' => $this->region,
            'telephone' => $this->telephone,
            'same_as_billing' => $this->same_as_billing,
            'save_in_address_book' => $this->save_in_address_book,
            'customer_address_id' => $this->customer_address_id,
        ];
    }
}
