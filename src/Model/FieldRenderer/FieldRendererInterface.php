<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\FieldRenderer;

use MageHx\MahxCheckout\Data\AddressFieldAttributes;

interface FieldRendererInterface
{
    public function render(AddressFieldAttributes $attributes): string;

    public function canRender(AddressFieldAttributes $attributes): bool;
}
