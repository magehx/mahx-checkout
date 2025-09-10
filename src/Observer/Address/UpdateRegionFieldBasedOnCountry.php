<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\Address;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageHx\MahxCheckout\Data\FormFieldConfig;
use MageHx\MahxCheckout\Enum\CheckoutForm;
use MageHx\MahxCheckout\Model\Config;
use MageHx\MahxCheckout\Model\CheckoutDataStorage;
use MageHx\MahxCheckout\Model\QuoteDetails;
use MageHx\MahxCheckout\Service\GenerateBlockHtml;
use MageHx\MahxCheckout\Service\PrepareRegionFieldAttribute;

/**
 * Adjusts the region field type (text/select) and renderer based on selected country.
 *
 * @event mahxcheckout_address_field_renderer_selected
 */
class UpdateRegionFieldBasedOnCountry implements ObserverInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly QuoteDetails $quote,
        private readonly CheckoutDataStorage $formDataStorage,
        private readonly GenerateBlockHtml $generateBlockHtmlService,
        private readonly PrepareRegionFieldAttribute $prepareRegionFieldAttributeService,
    ) {}

    public function execute(Observer $observer): void
    {
        if (!$this->isRegionFieldApplicable($observer)) {
            return;
        }

        /** @var FormFieldConfig $fieldConfig */
        $fieldConfig = $observer->getData('field_config');
        $formName = $observer->getData('form');
        $rendererData = $observer->getData('renderer_data');

        $country = $this->resolveCountryCode($formName);

        if (!$country) {
            return;
        }

        $this->configureRegionField($fieldConfig, $country);
        $this->selectRenderer($rendererData, $fieldConfig);
    }

    private function isRegionFieldApplicable(Observer $observer): bool
    {
        $field = $observer->getData('field_config');
        $form = $observer->getData('form');

        return $field instanceof FormFieldConfig
            && $field->name === 'region'
            && in_array($form, [
                CheckoutForm::SHIPPING_ADDRESS->value,
                CheckoutForm::BILLING_ADDRESS->value,
                CheckoutForm::NEW_ADDRESS->value,
            ], true);
    }

    private function resolveCountryCode(string $form): ?string
    {
        if ($this->formDataStorage->hasData('country_id')) {
            return $this->formDataStorage->getData('country_id');
        }

        if ($this->quote->isBillingSameAsShipping() || $form === CheckoutForm::SHIPPING_ADDRESS->value) {
            return $this->quote->getShippingAddress()->getCountry() ?: $this->config->getDefaultShippingCountry();
        }

        return $this->quote->getBillingAddress()->getCountry() ?: $this->config->getDefaultBillingCountry();
    }

    private function configureRegionField(FormFieldConfig &$regionField, string $countryCode): void
    {
        $regionField = $this->prepareRegionFieldAttributeService->execute($countryCode, regionField: $regionField);

        if ($regionField->form === CheckoutForm::NEW_ADDRESS->value) {
            $regionField = $this->prepareRegionFieldAttributeService->addAdditionalAttributesToRegion(
                $regionField,
                $regionField->form
            );
        }

        $regionField->meta->wrapperElemExtraAttributes['id'] = "{$regionField->form}-region-section";
        $regionField->meta->wrapperElemExtraClasses = 'relative';
        $regionField->value = (string) $this->resolveRegionValue($regionField);

        $loaderId = "{$regionField->form}-region-loader";
        $regionField->meta->afterInputHtml = $this->generateBlockHtmlService->getLoaderHtml(
            $loaderId,
            'est-shipping-country-loader'
        );

        if ($regionField->required) {
            $regionField->meta->inputElementExtraAttributes = [
                ...$regionField->meta->inputElementExtraAttributes,
                'data-is-required' => true,
            ];
        }
    }

    private function resolveRegionValue(FormFieldConfig $regionField): ?string
    {
        if ($this->formDataStorage->hasData('country_id')) {
            return ''; // reset if form is being driven by form input, not quote
        }

        $address = $regionField->form === CheckoutForm::SHIPPING_ADDRESS->value
            ? $this->quote->getShippingAddress()
            : $this->quote->getBillingAddress();

        return $address->getRegionId() ?: $address->getRegion();
    }

    private function selectRenderer(DataObject &$rendererData, FormFieldConfig $field): void
    {
        $renderers = $rendererData->getData('renderer_list') ?? [];

        foreach ($renderers as $renderer) {
            if ($renderer->canRender($field)) {
                $rendererData->setData('selected_renderer', $renderer);
                break;
            }
        }
    }
}
