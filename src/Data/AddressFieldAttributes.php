<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data;

use Rkt\MageData\Data;

class AddressFieldAttributes extends Data
{
    public function __construct(
        public string $name,
        public string $label,
        public string $type,
        public bool $required,
        public string $form,
        public int|string|array $value = '',
        public ?array $rules = null,
        public ?int $sortOrder = 100,
        public ?array $additionalData = []
    ) {
    }

    public function getFieldId(): string
    {
        return $this->additionalData['id'] ?? $this->form . '-' . $this->name;
    }
}
