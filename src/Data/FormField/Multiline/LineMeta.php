<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data\FormField\Multiline;

use Rkt\MageData\Data;

class LineMeta extends Data
{
    public function __construct(
        public string $label = '',
        public bool $required = false,
        public int $width = 100,
    ) {
    }

    public function getSpanWidth(): int
    {
        return match (true) {
            $this->width > 75 => 4,
            $this->width > 50 => 3,
            $this->width > 25 => 2,
            $this->width <= 25 => 1,
        };
    }
}
