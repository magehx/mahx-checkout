<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\Theme;

use MageHx\MahxCheckout\Model\Config;

class ActiveCheckoutThemeResolver
{
    private ?CheckoutThemeRegistry $checkoutThemeRegistry = null;

    public function __construct(
        private readonly Config $config,
        private readonly CheckoutThemeRegistryFactory $checkoutThemeRegistryFactory,
    ) {
    }

    public function resolve(): CheckoutThemeInterface
    {
        if (!$this->checkoutThemeRegistry) {
            $this->checkoutThemeRegistry = $this->checkoutThemeRegistryFactory->create();
        }

        return $this->checkoutThemeRegistry->get($this->config->getActiveTheme());
    }
}
