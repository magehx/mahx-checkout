<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use MageHx\MahxCheckout\Data\TotalItemData;
use MageHx\MahxCheckout\Service\TotalsInformationManagement;

class Totals implements ArgumentInterface
{
    public function __construct(
        private readonly PriceCurrencyInterface $priceCurrency,
        private readonly TotalsInformationManagement $totalsInformationManagement,
    ) {}

    /**
     * @return TotalItemData[]
     */
    public function getTotals(): array
    {
        return $this->totalsInformationManagement->getTotals();
    }

    public function getGrandTotal(): ?TotalItemData
    {
        foreach (array_reverse($this->getTotals()) as $total) {
            if ($total->code === 'grand_total') {
                return $total;
            }
        }

        return null;
    }

    public function formatPrice(float $amount): string
    {
        return $this->priceCurrency->format($amount, false);
    }

    public function getHtmxLoaderClasses(): array
    {
        return [
            'save-shipping-loader',
            'est-shipping-loader',
            'est-shipping-country-loader'
        ];
    }
}
