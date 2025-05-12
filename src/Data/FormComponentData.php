<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data;

use Rkt\MageData\Data;

class FormComponentData extends Data
{
    public function __construct(
        public string $name,
        public string $label,
    ) {
    }
}
