<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data;

use Rkt\MageData\Data;

class ValidationMapperData extends Data
{
    public function __construct(
        public ?array $rules = [],
        public ?array $messages = [],
        public ?array $aliases = [],
    ) {
    }

    public function exportToJs(): array
    {
        $export = [];

        if (!empty($this->rules)) {
            $export['rules'] = $this->rules;
        }
        if (!empty($this->messages)) {
            $export['messages'] = $this->messages;
        }
        if (!empty($this->aliases)) {
            $export['aliases'] = $this->aliases;
        }

        return $export;
    }
}
