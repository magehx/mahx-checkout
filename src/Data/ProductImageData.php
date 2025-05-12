<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data;

use Rkt\MageData\Data;

class ProductImageData extends Data
{
    public function __construct(
        public string $src,
        public ?string $alt,
        public int $width,
        public int $height,
    ) {
    }
}
