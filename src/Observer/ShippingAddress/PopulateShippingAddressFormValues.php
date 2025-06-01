<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\ShippingAddress;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageHx\MahxCheckout\Data\FormFieldConfig;
use MageHx\MahxCheckout\Enum\CheckoutForm;
use MageHx\MahxCheckout\Model\Config;
use MageHx\MahxCheckout\Model\QuoteDetails;

class PopulateShippingAddressFormValues implements ObserverInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly QuoteDetails $quote
    ) {
    }

    public function execute(Observer $observer): void
    {
        /** @var DataObject $transport */
        $transport = $observer->getData('transport');

        /** @var FormFieldConfig[] $addressFields */
        $addressFields = $transport->getData('fields');
        $formId = $transport->getData('form_id');

        // Handle default country if it's not the shipping address form; eg: it can be a new address form
        if ($formId !== CheckoutForm::SHIPPING_ADDRESS->value) {
            $this->setDefaultCountryIfAvailable($addressFields);
            $transport->setData('fields', $addressFields);
            return;
        }

        $shippingAddress = $this->quote->getShippingAddress();

        foreach ($addressFields as $field) {
            $fieldName = $field->name;

            $value = match ($fieldName) {
                'street'     => $shippingAddress->getStreet(),
                'country_id' => $shippingAddress->getCountryId() ?: $this->config->getDefaultShippingCountry(),
                default      => $shippingAddress->getData($fieldName) ?? '',
            };

            $field->value = $value;
        }

        $transport->setData('fields', $addressFields);
    }

    private function setDefaultCountryIfAvailable(array &$fields): void
    {
        if (isset($fields['country_id'])) {
            $fields['country_id']->value = $this->config->getDefaultShippingCountry();
        }
    }
}
