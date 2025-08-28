<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data\FormField;

use MageHx\MahxCheckout\Data\FormField\Multiline\LineMeta;

class MultilineFieldMeta extends BaseFormFieldMeta
{
    /**
     * @param LineMeta[] $lines
     */
    public function __construct(
        public int $lineCount = 1,
        public array $lines = [],
    ) {
    }
}
