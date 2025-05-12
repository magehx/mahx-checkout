<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\Address;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageHx\MahxCheckout\Data\AddressFieldAttributes;
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
        /** @var AddressFieldAttributes[] $addressFields */
        $addressFields = $observer->getData('transport')->getData('fields');
        $countryField = $this->findCountryField($addressFields);

        if (!$countryField) {
            return;
        }

        $this->addCountryOptions($countryField);
    }

    private function findCountryField(array &$addressFields): ?AddressFieldAttributes
    {
        return current(array_filter($addressFields, fn($field) => $field->name === 'country_id')) ?: null;
    }

    private function addCountryOptions(AddressFieldAttributes &$countryField): void
    {
        $countryField->additionalData['options'] =
            $this->countryProvider->getStoreAllowedCountriesOption($this->quote->getStoreId());
        $countryField->additionalData['defaultOptionLabel'] = 'Please select your country';
    }
}
