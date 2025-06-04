<?php

namespace MageHx\MahxCheckout\Model\Theme;

use Magento\Framework\Exception\LocalizedException;

class CheckoutThemeRegistry
{
    /**
     * @var CheckoutThemeInterface[]
     */
    private array $themes;

    public function __construct(array $themes = [])
    {
        $this->themes = [];

        foreach ($themes as $theme) {
            if (!$theme instanceof CheckoutThemeInterface) {
                throw new \InvalidArgumentException(get_class($theme) . ' must implement CheckoutThemeInterface');
            }

            $this->themes[$theme->getCode()] = $theme;
        }
    }

    public function get(string $code): CheckoutThemeInterface
    {
        if (!isset($this->themes[$code])) {
            throw new LocalizedException(__('Checkout theme "%1" is not registered.', $code));
        }

        return $this->themes[$code];
    }

    /**
     * @return CheckoutThemeInterface[]
     */
    public function getAll(): array
    {
        return array_values($this->themes);
    }

    /**
     * @return string[]
     */
    public function getCodes(): array
    {
        return array_keys($this->themes);
    }
}

