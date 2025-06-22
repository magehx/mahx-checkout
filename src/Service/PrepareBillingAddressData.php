<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use MageHx\MahxCheckout\Data\AddressData;

class PrepareBillingAddressData
{
    public function prepare(array $data, bool $isSameAsShipping = true): AddressData
    {
        return AddressData::from([
            'firstname' => $data['firstname'] ?? '',
            'lastname' => $data['lastname'] ?? '',
            'street' => $data['street'] ?? [],
            'city' => $data['city'] ?? '',
            'country_id' => $data['country_id'] ?? '',
            'postcode' => $data['postcode'] ?? '',
            'telephone' => $data['telephone'] ?? '',
            'region' => $data['region'] ?? '',
            'same_as_billing' => $isSameAsShipping,
        ]);
    }
}
