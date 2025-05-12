<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\ShippingAddress;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageHx\MahxCheckout\Data\AddressFieldAttributes;
use MageHx\MahxCheckout\Enum\CheckoutForm;
use MageHx\MahxCheckout\Model\FormDataStorage;
use MageHx\MahxCheckout\Model\ShippingAddressForm;

class RePopulateShippingAddressWithFormData implements ObserverInterface
{
    public function __construct(private readonly FormDataStorage $formDataStorage)
    {
    }

    public function execute(Observer $observer): void
    {
        if (!$this->isRequiredToRepopulateField($observer)) {
            return;
        }

        $this->rePopulateFieldWithFormData($observer);
    }

    private function isRequiredToRepopulateField(Observer $observer): bool
    {
        $fieldAttributes = $this->getFieldAttributes($observer);

        return $observer->getData('form') === CheckoutForm::SHIPPING_ADDRESS->value
            && !empty($this->formDataStorage->getData($fieldAttributes->name));
    }

    private function rePopulateFieldWithFormData(Observer $observer): void
    {
        $fieldAttributes = $this->getFieldAttributes($observer);

        $fieldAttributes->value = $this->formDataStorage->getData($fieldAttributes->name);
    }

    private function getFieldAttributes(Observer $observer): AddressFieldAttributes
    {
        return $observer->getData('field_attributes');
    }
}
