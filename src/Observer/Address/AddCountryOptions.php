<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\Address;

use MageHx\MahxCheckout\Data\FormField\SelectFieldMeta;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageHx\MahxCheckout\Data\FormFieldConfig;
use MageHx\MahxCheckout\Model\CountryProvider;
use MageHx\MahxCheckout\Model\QuoteDetails;

/**
 * @event mahxcheckout_address_form_fields_prepared
 */
class AddCountryOptions implements ObserverInterface
{
    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly CountryProvider $countryProvider
    ) {}

    public function execute(Observer $observer): void
    {
        /** @var FormFieldConfig[] $addressFields */
        $addressFields = $observer->getData('transport')->getData('fields');
        $countryField = $this->findCountryField($addressFields);

        if (!$countryField) {
            return;
        }

        $this->addCountryOptions($countryField);
    }

    private function findCountryField(array &$addressFields): ?FormFieldConfig
    {
        return current(array_filter($addressFields, fn($field) => $field->name === 'country_id')) ?: null;
    }

    private function addCountryOptions(FormFieldConfig &$countryField): void
    {
        $countryField->meta = SelectFieldMeta::from([
            'options' => $this->countryProvider->getStoreAllowedCountriesOption($this->quote->getStoreId()),
            'defaultOptionLabel' => __('Please select your country'),

        ]);
    }
}
