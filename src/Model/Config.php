<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model;

use Magento\Directory\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const MAHXCHECKOUT_ENABLED_CONFIG_PATH = 'mahxcheckout/general/enabled';

    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
    }

    public function isCartItemPriceIncludesTax(): bool
    {
        return $this->scopeConfig->isSetFlag(
            \Magento\Tax\Model\Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function canShowCartItemsTotalQty(): bool
    {
        return $this->scopeConfig->isSetFlag('checkout/cart_link/use_qty', ScopeInterface::SCOPE_STORE);
    }

    public function getDefaultShippingCountry(): string
    {
        return $this->scopeConfig->getValue(Data::XML_PATH_DEFAULT_COUNTRY, ScopeInterface::SCOPE_STORE) ?:
            $this->scopeConfig->getValue(
                \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_COUNTRY,
                ScopeInterface::SCOPE_STORE
            ) ?: '';
    }

    public function getDefaultBillingCountry(): string
    {
        return $this->getDefaultShippingCountry();
    }

    public function getDefaultShippingPostcode(): ?string
    {
        return (string) $this->scopeConfig->getValue(
            \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_COUNTRY,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getShippingPriceTaxDisplay(): int
    {
        return (int) $this->scopeConfig->getValue(
            \Magento\Tax\Model\Config::CONFIG_XML_PATH_DISPLAY_SHIPPING,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getPaymentTitle(string $code): string
    {
        return (string) $this->scopeConfig->getValue('payment/' . $code . '/title', ScopeInterface::SCOPE_STORE);
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::MAHXCHECKOUT_ENABLED_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }
}
