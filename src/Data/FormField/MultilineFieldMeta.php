<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data\FormField;

class MultilineFieldMeta extends BaseFormFieldMeta
{
    public function __construct(
        public ?int $lineCount = 1,
    ) {
    }
}
