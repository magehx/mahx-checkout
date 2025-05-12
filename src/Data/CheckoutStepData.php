<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data;

use Rkt\MageData\Data;

class CheckoutStepData extends Data
{
    /**
     * @param FormComponentData[] $formComponents
     */
    public function __construct(
        public int $order,
        public string $name,
        public string $label,
        public string $urlHash,
        public string $layoutHandle,
        public bool $isDefault = false,
        public bool $isValid = false,
        public ?string $saveDataUrl = null,
        public ?string $buttonLabel = null,
        public array $formComponents = [],
    ) {
    }
}
