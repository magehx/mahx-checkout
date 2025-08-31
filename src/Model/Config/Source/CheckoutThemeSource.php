<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use MageHx\MahxCheckout\Model\Theme\CheckoutThemeRegistry;

class CheckoutThemeSource implements OptionSourceInterface
{
    public function __construct(
        private readonly CheckoutThemeRegistry $checkoutThemeRegistry
    ) {}

    public function toOptionArray(): array
    {
        return array_map(
            fn ($theme) => ['value' => $theme->getCode(), 'label' => $theme->getLabel()],
            $this->checkoutThemeRegistry->getAll()
        );
    }
}
