<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data\Address;

use Rkt\MageData\Data;

class CardData extends Data
{
    public function __construct(
        public string|int $addressId,
        public AddressLinesData $addressLines,
        public bool $isSelected = false,
    ) {}

    public function isNew(): bool
    {
        return $this->addressId === 'new';
    }
}
