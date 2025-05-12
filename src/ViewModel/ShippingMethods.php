<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Tax\Model\Config as TaxConfig;
use MageHx\MahxCheckout\Model\Config;
use MageHx\MahxCheckout\Model\QuoteDetails;

class ShippingMethods implements ArgumentInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly QuoteDetails $quote,
        private readonly PriceCurrencyInterface $priceCurrency,
    ) {
    }

    public function getMethodPriceHtml(ShippingMethodInterface $method): string
    {
        $displayMode = $this->config->getShippingPriceTaxDisplay();

        return match ($displayMode) {
            TaxConfig::DISPLAY_TYPE_INCLUDING_TAX => $this->priceCurrency->format($method->getPriceInclTax()),
            default => $this->priceCurrency->format($method->getPriceExclTax())
        };
    }

    public function getInputValueFromMethod(ShippingMethodInterface $method): string
    {
        return $method->getCarrierCode() . '_' . $method->getMethodCode();
    }

    public function getShippingMethodDetails(): string
    {
        return $this->quote->getShippingMethodDescription();
    }

    public function getSelectedMethod(): ?string
    {
        return $this->quote->getShippingMethod();
    }
}
