<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\BillingAddress;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageHx\MahxCheckout\Data\AddressFieldAttributes;
use MageHx\MahxCheckout\Enum\AdditionalFieldAttribute;

class AddAdditionalAttributesToCountryField implements ObserverInterface
{

    public function execute(Observer $observer): void
    {
        /** @var AddressFieldAttributes $fields */
        $fields = $observer->getData('fields');
        $countryField = $fields['country_id'] ?? null;

        if (!$countryField) {
            return;
        }

        $countryField->additionalData[AdditionalFieldAttribute::INPUT_EXTRA_ATTRIBUTES->value] = [
            '@change' => 'handleCountryChange'
        ];
    }
}
