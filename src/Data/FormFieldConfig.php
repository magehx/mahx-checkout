<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data;

use MageHx\MahxCheckout\Data\FormField\BaseFormFieldMeta;
use Rkt\MageData\Data;

class FormFieldConfig extends Data
{
    public function __construct(
        public string $name,
        public string $label,
        public string $type,
        public bool $required,
        public string $form,
        public ?string $id = null,
        public int|string|array $value = '',
        public ?int $sortOrder = 100,
        public ?BaseFormFieldMeta $meta = null,
    ) {
        if ($this->meta === null) {
            $this->meta = new BaseFormFieldMeta();
        }
    }

    public function getFieldId(): string
    {
        return $this->id ?? $this->form . '-' . $this->name;
    }
}
