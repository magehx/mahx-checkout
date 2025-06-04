<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\Theme;

use MageHx\MahxCheckout\Model\Config;

class ActiveCheckoutThemeResolver
{
    public function __construct(
        private readonly Config $config,
        private readonly CheckoutThemeRegistry $checkoutThemeRegistry,
    ) {
    }

    public function resolve(): CheckoutThemeInterface
    {
        return $this->checkoutThemeRegistry->get($this->config->getActiveTheme());
    }
}
