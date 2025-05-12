<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data;

use Rkt\MageData\Data;

class OrderSummaryCartItem extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public int $qty,
        public string $price,
        public ?ProductImageData $image,
        public ?array $options = [],
    ) {
    }
}
