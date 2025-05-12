<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Session as CustomerSession;

class CustomerAddressService
{
    public function __construct(
        private readonly CustomerSession $customerSession,
        private readonly CustomerRepositoryInterface $customerRepository,
    ) {
    }

    /**
     * @return AddressInterface[]
     */
    public function getCurrentCustomerAddressList(): array
    {
        if (!$this->customerSession->isLoggedIn()) {
            return [];
        }

        $customerId = $this->customerSession->getCustomerId();
        $customer = $this->customerRepository->getById($customerId);

        return $customer->getAddresses();
    }

    public function getCurrentCustomerDefaultShippingAddress(): ?AddressInterface
    {
        foreach ($this->getCurrentCustomerAddressList() as $address) {
            if ($address->isDefaultShipping()) {
                return $address;
            }
        }

        return null;
    }

    public function getCurrentCustomerDefaultBillingAddress(): ?AddressInterface
    {
        foreach ($this->getCurrentCustomerAddressList() as $address) {
            if ($address->isDefaultBilling()) {
                return $address;
            }
        }

        return null;
    }

    public function isCurrentCustomerHoldsAddress(): bool
    {
        return count($this->getCurrentCustomerAddressList()) > 0;
    }

    public function getCurrentCustomerAddressById(int $id): ?AddressInterface
    {
        foreach ($this->getCurrentCustomerAddressList() as $address) {
            if ($address->getId() == $id) {
                return $address;
            }
        }

        return null;
    }
}
