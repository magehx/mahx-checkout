<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\ShippingAddress;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use MageHx\MahxCheckout\Data\AddressFieldAttributes;
use MageHx\MahxCheckout\Enum\AdditionalFieldAttribute;
use MageHx\MahxCheckout\Enum\CheckoutForm;

class AddHtmxAttributesToInputsForShippingEstimation implements ObserverInterface
{
    private const ESTIMATE_ON_FIELDS = ['country_id', 'region', 'postcode'];

    public function __construct(private readonly UrlInterface $urlBuilder)
    {
    }

    public function execute(Observer $observer): void
    {
        if (!$this->canApplyHtmxAttributes($observer)) {
            return;
        }

        /** @var AddressFieldAttributes $fieldAttributes */
        $fieldAttributes = $observer->getData('field_attributes');

        $fieldAttributes->additionalData[AdditionalFieldAttribute::INPUT_EXTRA_ATTRIBUTES->value] =
            $this->getHtmxAttributes($fieldAttributes->name);
    }

    /**
     * Determine if attributes should be applied.
     */
    private function canApplyHtmxAttributes(Observer $observer): bool
    {
        $form = $observer->getData('form');
        $fieldAttributes = $observer->getData('field_attributes');

        return $form === CheckoutForm::SHIPPING_ADDRESS->value && in_array($fieldAttributes->name, self::ESTIMATE_ON_FIELDS, true);
    }

    /**
     * Generate HTMX attributes dynamically.
     */
    private function getHtmxAttributes(string $fieldName): array
    {
        $formId = CheckoutForm::SHIPPING_ADDRESS->value;

        return [
            'hx-target' => "#shipping-methods-section",
            'hx-swap' => "outerHTML",
            'hx-trigger' => "mahxcheckout-{$formId}-{$fieldName}-validated from:body delay:300ms",
            'hx-indicator' => $this->isCountryField($fieldName) ? ".estimate-shipping-loader" : "#shipping-loader",
            'hx-include' => '#guest-email-form',
            'hx-post' => $this->urlBuilder->getUrl("mahxcheckout/form/estimateShippingMethods"),
            '@change' => "estimateShippingMethods",
        ];
    }

    private function isCountryField(string $fieldName): bool
    {
        return $fieldName === 'country_id';
    }
}
