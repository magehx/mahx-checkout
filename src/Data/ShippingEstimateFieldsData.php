<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data;

use Rkt\MageData\Data;

class ShippingEstimateFieldsData extends Data
{
    public function __construct(
        public ?string $postcode = null,
        public ?string $country = null,
        public ?string $region = null,
    ) {
    }

    public function rules(): array
    {
        return [
            'country' => 'required|max:2',
        ];
    }
}
