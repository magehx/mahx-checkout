<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\ShippingAddress;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageHx\MahxCheckout\Data\AddressFieldAttributes;
use MageHx\MahxCheckout\Enum\CheckoutForm;

/**
 * @event mahxcheckout_shipping_address_form_fields_prepared
 */
class AddSaveAddressFieldForCustomerWithNoAddress implements ObserverInterface
{
    public function __construct(
        private readonly CustomerSession $customerSession,
    ) {
    }

    public function execute(Observer $observer): void
    {
        if (!$this->customerSession->isLoggedIn() || $this->hasCustomerAddress()) {
            return;
        }

        $this->addSaveInAddressBookFieldToShippingAddressForm($observer);
    }

    private function hasCustomerAddress(): bool
    {
        $customer = $this->customerSession->getCustomer();

        return count($customer->getAddresses()) > 0;
    }

    private function addSaveInAddressBookFieldToShippingAddressForm(Observer $observer): void
    {
        /** @var AddressFieldAttributes[] $fields */
        /** @var DataObject $transport */
        $transport = $observer->getData('transport');
        $fields = $transport->getData('fields');
        $fields['save_in_address_book'] = new AddressFieldAttributes(
            name: 'save_in_address_book',
            label: 'Save In Address Book',
            type: 'hidden',
            required: true,
            form: CheckoutForm::SHIPPING_ADDRESS->value,
            value: '1',
        );
        $transport->setData('fields', $fields);
    }
}
