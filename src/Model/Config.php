<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model;

use MageHx\MahxCheckout\Data\Config\AddressPrefixConfig;
use MageHx\MahxCheckout\Data\Config\AddressSuffixConfig;
use MageHx\MahxCheckout\Enum\AddressAttributeShow;
use MageHx\MahxCheckout\Enum\YesNo;
use Magento\Directory\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const MAHXCHECKOUT_ENABLED_CONFIG_PATH = 'mahxcheckout/general/enabled';
    const STREET_LINES_COUNT_CONFIG_PATH = 'customer/address/street_lines';
    const TELEPHONE_SHOW_CONFIG_PATH = 'customer/address/telephone_show';
    const PREFIX_SHOW_CONFIG_PATH = 'customer/address/prefix_show';
    const PREFIX_OPTIONS_CONFIG_PATH = 'customer/address/prefix_options';
    const SUFFIX_SHOW_CONFIG_PATH = 'customer/address/suffix_show';
    const SUFFIX_OPTIONS_CONFIG_PATH = 'customer/address/suffix_options';
    const MIDDLE_NAME_SHOW_CONFIG_PATH = 'customer/address/middlename_show';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isCartItemPriceIncludesTax(): bool
    {
        return $this->isStoreSetFlag(\Magento\Tax\Model\Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX);
    }

    public function canShowCartItemsTotalQty(): bool
    {
        return $this->isStoreSetFlag('checkout/cart_link/use_qty');
    }

    public function getDefaultShippingCountry(): string
    {
        return $this->getStoreConfig(Data::XML_PATH_DEFAULT_COUNTRY) ?:
            $this->getStoreConfig(\Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_COUNTRY) ?: '';
    }

    public function getDefaultBillingCountry(): string
    {
        return $this->getDefaultShippingCountry();
    }

    public function getDefaultShippingPostcode(): ?string
    {
        return (string) $this->getStoreConfig(\Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_COUNTRY);
    }

    public function getShippingPriceTaxDisplay(): int
    {
        return (int) $this->getStoreConfig(\Magento\Tax\Model\Config::CONFIG_XML_PATH_DISPLAY_SHIPPING);
    }

    public function getPaymentTitle(string $code): string
    {
        return (string) $this->getStoreConfig('payment/' . $code . '/title');
    }

    public function isEnabled(): bool
    {
        return $this->isStoreSetFlag(self::MAHXCHECKOUT_ENABLED_CONFIG_PATH);
    }

    public function getStreetLinesCount(): int
    {
        return (int) $this->getStoreConfig(self::STREET_LINES_COUNT_CONFIG_PATH);
    }

    public function getTelephoneShow(): ?AddressAttributeShow
    {
        return AddressAttributeShow::tryFrom($this->getStoreConfig(self::TELEPHONE_SHOW_CONFIG_PATH) ?? '');
    }

    public function getPrefixConfig(): AddressPrefixConfig
    {
        return AddressPrefixConfig::from([
            'options' => $this->getStoreConfig(self::PREFIX_OPTIONS_CONFIG_PATH) ?: '',
            'show' => AddressAttributeShow::tryFrom($this->getStoreConfig(self::PREFIX_SHOW_CONFIG_PATH)),
        ]);
    }

    public function getSuffixConfig(): AddressSuffixConfig
    {
        return AddressSuffixConfig::from([
            'options' => $this->getStoreConfig(self::SUFFIX_OPTIONS_CONFIG_PATH) ?: '',
            'show' => AddressAttributeShow::tryFrom($this->getStoreConfig(self::SUFFIX_SHOW_CONFIG_PATH)),
        ]);
    }

    public function getMiddleNameShow(): YesNo
    {
        return YesNo::tryFrom((int)$this->isStoreSetFlag(self::MIDDLE_NAME_SHOW_CONFIG_PATH));
    }

    public function getStoreConfig(string $path): mixed
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    public function isStoreSetFlag(string $path): bool
    {
        return $this->scopeConfig->isSetFlag($path, ScopeInterface::SCOPE_STORE);
    }
}
