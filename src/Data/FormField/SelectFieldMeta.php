<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data\FormField;

use Magento\Framework\Phrase;

class SelectFieldMeta extends BaseFormFieldMeta
{
    public function __construct(
        public array $options = [],
        public string|Phrase $defaultOptionLabel = 'Select',
    ) {
    }
}
