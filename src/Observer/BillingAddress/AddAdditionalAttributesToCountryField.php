<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\BillingAddress;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageHx\MahxCheckout\Data\FormFieldConfig;

/**
 * @event mahxcheckout_billing_address_form_fields_prepared
 */
class AddAdditionalAttributesToCountryField implements ObserverInterface
{

    public function execute(Observer $observer): void
    {
        /** @var FormFieldConfig[] $fields */
        /** @var DataObject $transport */
        $transport = $observer->getData('transport');
        $fields = $transport->getData('fields');
        $countryField = $fields['country_id'] ?? null;

        if (!$countryField) {
            return;
        }

        $countryField->meta->inputElementExtraAttributes = ['@change' => 'handleCountryChange'];
        $transport->setData('fields', $fields);
    }
}
