<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\FieldRenderer;

use MageHx\MahxCheckout\Data\FormFieldConfig;

interface FieldRendererInterface
{
    public function render(FormFieldConfig $attributes): string;

    public function canRender(FormFieldConfig $attributes): bool;
}
