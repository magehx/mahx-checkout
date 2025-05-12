<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\DataObject;
use Magento\Tax\Helper\Data as TaxHelper;
use MageHx\MahxCheckout\Data\TotalItemData;
use MageHx\MahxCheckout\Model\EventDispatcher;
use MageHx\MahxCheckout\Model\QuoteDetails;
use Magento\Quote\Api\Data\TotalSegmentInterface;
use Magento\Quote\Api\Data\TotalsInterface;

class TotalsInformationManagement
{
    private ?array $totalsSummary = null;

    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly TaxHelper $taxHelper,
        private readonly EventDispatcher $eventDispatcher,
        private readonly PaymentInformationManagementInterface $paymentInformationManagement,
    ) {}

    /**
     * @return TotalItemData[]
     */
    public function getTotals(): array
    {
        if (!empty($this->totalsSummary)) {
            return $this->totalsSummary;
        }

        $paymentDetails = $this->paymentInformationManagement->getPaymentInformation($this->quote->getId());
        $totals = $paymentDetails->getTotals();
        $this->totalsSummary = [];

        $totalsEventData = new DataObject(['totals' => $totals]);
        $this->eventDispatcher->dispatchTotalsDataPrepareBefore(['transport' => $totalsEventData]);

        foreach ($totalsEventData->getData('totals')->getTotalSegments() as $segment) {
            $this->totalsSummary = [...$this->totalsSummary, ...$this->processSegment($segment, $totals)];
        }

        $totalsSummaryData = new DataObject(['totals' => $this->totalsSummary]);
        $this->eventDispatcher->dispatchTotalsDataPrepareAfter(['transport' => $totalsSummaryData]);
        $this->totalsSummary = $totalsSummaryData->getData('totals');

        return $this->totalsSummary;
    }

    /**
     * @return TotalItemData[]
     */
    private function processSegment(TotalSegmentInterface $segment, TotalsInterface $totals): array
    {
        return match ($segment->getCode()) {
            'subtotal'    => $this->handleSubtotal($segment, $totals),
            'shipping'    => $this->handleShipping($segment),
            'tax'         => $this->handleTax($segment),
            'grand_total' => [TotalItemData::from([
                'code'  => $segment->getCode(),
                'label' => __('Grand Total'),
                'value' => $segment->getValue(),
            ])],
            default => [TotalItemData::from([
                'code'  => $segment->getCode(),
                'label' => $segment->getTitle(),
                'value' => $segment->getValue(),
            ])],
        };
    }

    /**
     * @return TotalItemData[]
     */
    private function handleSubtotal(TotalSegmentInterface $segment, TotalsInterface $totals): array
    {
        return match (true) {
            $this->taxHelper->displayCartBothPrices() => [
                TotalItemData::from([
                    'code'  => $segment->getCode(),
                    'label' => __('Subtotal (Excl. Tax)'),
                    'value' => $segment->getValue(),
                ]),
                TotalItemData::from([
                    'code'  => $segment->getCode(),
                    'label' => __('Subtotal (Incl. Tax)'),
                    'value' => $totals->getSubtotalInclTax(),
                ]),
            ],
            $this->taxHelper->displayCartPriceInclTax() => [
                TotalItemData::from([
                    'code'  => $segment->getCode(),
                    'label' => __('Subtotal (Incl. Tax)'),
                    'value' => $totals->getSubtotalInclTax(),
                ]),
            ],
            default => [
                TotalItemData::from([
                    'code'  => $segment->getCode(),
                    'label' => __('Subtotal'),
                    'value' => $segment->getValue(),
                ]),
            ]
        };
    }

    /**
     * @return TotalItemData[]
     */
    private function handleShipping(TotalSegmentInterface $segment): array
    {
        $base = [
            'code'  => $segment->getCode(),
            'value' => $segment->getValue(),
        ];
        $shippingMethod = $this->quote->getShippingMethodDescription();
        $withShippingMethod = fn ($label) => __($label) . "<p class='block italic'>{$shippingMethod}</p>";

        $inclTax = $segment->getValue() + $segment->getTaxAmount();

        return match (true) {
            $this->taxHelper->displayShippingBothPrices() => [
                TotalItemData::from(['label' => $withShippingMethod('Shipping (Excl. Tax)'), ...$base]),
                TotalItemData::from(['label' => $withShippingMethod('Shipping (Incl. Tax)'), 'value' => $inclTax, 'code' => $base['code']]),
            ],
            $this->taxHelper->displayShippingPriceIncludingTax() => [
                TotalItemData::from(['label' => $withShippingMethod('Shipping (Incl. Tax)'), 'value' => $inclTax, 'code' => $base['code']]),
            ],
            default => [
                TotalItemData::from(['label' => $withShippingMethod('Shipping'), ...$base]),
            ],
        };
    }

    /**
     * @return TotalItemData[]
     */
    private function handleTax(TotalSegmentInterface $segment): array
    {
        if ($segment->getValue() <= 0 && ! $this->taxHelper->displayZeroTax()) {
            return [];
        }

        $items = [
            TotalItemData::from([
                'code'  => $segment->getCode(),
                'label' => __('Tax'),
                'value' => $segment->getValue(),
            ])
        ];

        if ($this->taxHelper->displayFullSummary()) {
            foreach ($segment->getExtensionAttributes()?->getTaxGrandtotalDetails() ?? [] as $detail) {
                $items[] = TotalItemData::from([
                    'code'  => $segment->getCode(),
                    'label' => $detail->getTitle(),
                    'value' => $detail->getAmount(),
                ]);
            }
        }

        return $items;
    }
}
