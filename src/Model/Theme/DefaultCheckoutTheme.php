<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\Theme;

/**
 * Default checkout theme implementation based on Luma-like two-step flow.
 */
class DefaultCheckoutTheme extends CheckoutThemeAbstract
{
    public const THEME_CODE = 'default';

    public function getCode(): string
    {
        return self::THEME_CODE;
    }

    public function getLabel(): string
    {
        return 'Default Checkout';
    }
}
