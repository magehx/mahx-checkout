<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterfaceFactory;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use MageHx\MahxCheckout\Data\ShippingInformation;

class SaveShippingInformation
{
    public function __construct(
        private readonly CustomerSession $customerSession,
        private readonly AddressInterfaceFactory $addressFactory,
        private readonly CustomerAddressService $customerAddressService,
        private readonly ShippingInformationInterfaceFactory $shippingInformationFactory,
        private readonly ShippingInformationManagementInterface $shippingInformationManagement,
    ) {
    }

    public function execute(int $cartId, ShippingInformation $shippingData): void
    {
        $address = $this->addressFactory->create(['data' => $shippingData->getAddress()]);

        /** @var ShippingInformationInterface $shippingInformation */
        $shippingInformation = $this->shippingInformationFactory->create();
        $shippingInformation->setShippingAddress($address);
        $shippingInformation->setBillingAddress($this->createBillingAddress($shippingData));
        $shippingInformation->setShippingMethodCode($shippingData->getShippingMethodCode());
        $shippingInformation->setShippingCarrierCode($shippingData->getShippingCarrierCode());

        $this->shippingInformationManagement->saveAddressInformation($cartId, $shippingInformation);
    }

    public function createBillingAddress(ShippingInformation $shippingData)
    {
        $billingAddressData = $shippingData->getAddress();

        /**
         * Customer logged-in scenario.
         *
         * Irrespective of the address data available for shipping address, we need to keep the billing address same as
         * the default billing address. So we are preparing billing address data from default billing address of
         * the customer here.
         */
        if ($this->customerSession->isLoggedIn() && $this->customerAddressService->isCurrentCustomerHoldsAddress()) {
            $customerBillingAddress = $this->customerAddressService->getCurrentCustomerDefaultBillingAddress();
            return $this->addressFactory->create(['data' => [
                'firstname' => $customerBillingAddress->getFirstname(),
                'lastname' => $customerBillingAddress->getLastname(),
                'street' => $customerBillingAddress->getStreet(),
                'city' => $customerBillingAddress->getCity(),
                'country_id' => $customerBillingAddress->getCountryId(),
                'postcode' => $customerBillingAddress->getPostcode(),
                'telephone' => $customerBillingAddress->getTelephone(),
                'region' => $customerBillingAddress->getRegion()->getRegion(),
                'customer_id' => (int)$this->customerSession->getCustomer()->getId(),
                'customer_address_id' => (int)$customerBillingAddress->getId(),
            ]]);
        }

        // Guest flow;
        unset($billingAddressData['same_as_billing']);
        unset($billingAddressData['save_in_address_book']);

        return $this->addressFactory->create(['data' => $billingAddressData]);
    }
}
