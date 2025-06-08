<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\ShippingAddress;

use MageHx\HtmxActions\Data\HxAttributesData;
use MageHx\HtmxActions\Enums\HtmxSwapOption;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use MageHx\MahxCheckout\Data\FormFieldConfig;
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

        /** @var FormFieldConfig $fieldConfig */
        $fieldConfig = $observer->getData('field_config');
        $fieldConfig->meta->inputElementExtraAttributes['@change'] = 'estimateShippingMethods';
        $fieldConfig->meta->inputElementHxAttributes = $this->prepareHtmxAttributes($fieldConfig->name);
    }

    /**
     * Determine if attributes should be applied.
     */
    private function canApplyHtmxAttributes(Observer $observer): bool
    {
        $form = $observer->getData('form');
        $fieldConfig = $observer->getData('field_config');

        return $form === CheckoutForm::SHIPPING_ADDRESS->value && in_array($fieldConfig->name, self::ESTIMATE_ON_FIELDS, true);
    }

    /**
     * Generate HTMX attributes dynamically.
     */
    private function prepareHtmxAttributes(string $fieldName): HxAttributesData
    {
        $formId = CheckoutForm::SHIPPING_ADDRESS->value;

        return HxAttributesData::from([
            'target' => "#shipping-methods-section",
            'swap' => HtmxSwapOption::outerHTML,
            'trigger' => "mahxcheckout-{$formId}-{$fieldName}-validated from:body delay:300ms",
            'indicator' => $this->isCountryField($fieldName) ? ".estimate-shipping-loader" : "#shipping-loader",
            'include' => ['#guest-email-form'],
            'post' => 'mahxcheckout/form/estimateShippingMethods',
        ]);
    }

    private function isCountryField(string $fieldName): bool
    {
        return $fieldName === 'country_id';
    }
}
