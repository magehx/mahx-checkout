<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data\Address;

use Rkt\MageData\Data;

class AddressLinesData extends Data
{
    public function __construct(
        public ?string $name = null,
        public ?string $streetLine1 = null,
        public int|string|null $postcode = null,
        public ?string $country = null,
        public ?string $company = null,
        public ?string $streetLine2 = null,
        public ?string $telephone = null,
        public ?string $city = null,
        public ?string $region = null,
    ) {}

    public function label(): string
    {
        return sprintf(
            '%s, %s, %s %s, %s, %s: %s',
            $this->name,
            $this->streetLine1,
            $this->streetLine2,
            $this->postcode,
            $this->country,
            __('Phone'),
            $this->telephone
        );
    }
}
