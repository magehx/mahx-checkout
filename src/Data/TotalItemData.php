<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data;

use Magento\Framework\Phrase;
use Rkt\MageData\Data;

class TotalItemData extends Data
{
    public function __construct(
        public string $code,
        public float $value,
        public string|Phrase $label,
    ) {
    }
}
