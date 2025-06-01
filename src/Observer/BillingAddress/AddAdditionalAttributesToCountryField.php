<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\BillingAddress;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageHx\MahxCheckout\Data\FormFieldConfig;

class AddAdditionalAttributesToCountryField implements ObserverInterface
{

    public function execute(Observer $observer): void
    {
        /** @var FormFieldConfig[] $fields */
        $fields = $observer->getData('fields');
        $countryField = $fields['country_id'] ?? null;

        if (!$countryField) {
            return;
        }

        $countryField->meta->inputElementExtraAttributes = ['@change' => 'handleCountryChange'];
    }
}
