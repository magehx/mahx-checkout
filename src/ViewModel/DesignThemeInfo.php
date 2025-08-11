<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

use MageHx\MahxCheckout\Service\CurrentDesignTheme;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class DesignThemeInfo implements ArgumentInterface
{
    public function __construct(
        private readonly CurrentDesignTheme $currentDesignTheme
    ) {}

    public function isHyvaTheme(): bool
    {
        return $this->currentDesignTheme->isHyva();
    }

    public function isMagentoTheme(): bool
    {
        return $this->currentDesignTheme->isCore();
    }
}
