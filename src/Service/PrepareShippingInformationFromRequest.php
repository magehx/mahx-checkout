<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Session as CustomerSession;
use MageHx\MahxCheckout\Data\ShippingInformation;
use MageHx\MahxCheckout\Model\QuoteDetails;

class PrepareShippingInformationFromRequest
{
    private ?array $postData = null;

    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly CustomerSession $customerSession,
        private readonly CustomerAddressService $customerAddressService,
        private readonly NewShippingAddressManager $newShippingAddressManager,
    ) {}

    public function execute(array $postData): ShippingInformation
    {
        $this->postData = $postData;

        return $this->shouldUseExistingCustomerAddress()
            ? $this->prepareFromExistingCustomerAddress()
            : $this->prepareFromNewOrGuestAddress();
    }

    private function prepareFromNewOrGuestAddress(): ShippingInformation
    {
        $isNewAddress = $this->isNewAddress();
        $isLoggedIn = $this->customerSession->isLoggedIn();
        $shippingAddress = $this->quote->getShippingAddress();

        $data = $isNewAddress
            ? $this->newShippingAddressManager->getNewAddress()
            : $this->postData;

        $street = $isNewAddress
            ? $shippingAddress->getStreet()
            : ($data['street'] ?? []);

        $region = $isNewAddress
            ? ($data['region_id'] ?? $data['region'] ?? '')
            : ($data['region'] ?? '');

        $isBillingSame = !$isLoggedIn || !$this->hasDefaultBillingAddress();

        return ShippingInformation::from([
            'method' => $this->post('method', ''),
            'address' => [
                'firstname' => $data['firstname'] ?? '',
                'lastname' => $data['lastname'] ?? '',
                'street' => $street,
                'city' => $data['city'] ?? '',
                'country_id' => $data['country_id'] ?? '',
                'postcode' => $data['postcode'] ?? '',
                'telephone' => $data['telephone'] ?? '',
                'region' => $region,
                'same_as_billing' => $isBillingSame,
                'save_in_address_book' => (int)($data['save_in_address_book'] ?? 0),
            ],
        ]);
    }

    private function prepareFromExistingCustomerAddress(): ShippingInformation
    {
        $addressId = (int) $this->postCustomerAddressId();
        $address = $this->customerAddressService->getCurrentCustomerAddressById($addressId);
        $billing = $this->getDefaultBillingAddress();

        $sameAsBilling = $billing && ((int) $address->getId() === (int) $billing->getId());

        return ShippingInformation::from([
            'method' => $this->post('method', ''),
            'address' => [
                'firstname' => $address->getFirstname(),
                'lastname' => $address->getLastname(),
                'street' => $address->getStreet(),
                'city' => $address->getCity(),
                'country_id' => $address->getCountryId(),
                'postcode' => $address->getPostcode(),
                'telephone' => $address->getTelephone(),
                'region' => $address->getRegion()->getRegion(),
                'customer_id' => (int) $address->getCustomerId(),
                'customer_address_id' => $addressId,
                'same_as_billing' => $sameAsBilling,
            ],
        ]);
    }

    private function post(string $key, mixed $default = null): mixed
    {
        return $this->postData[$key] ?? $default;
    }

    private function postCustomerAddressId(): ?string
    {
        return $this->post('customer_address_id');
    }

    private function isNewAddress(): bool
    {
        return $this->postCustomerAddressId() === 'new';
    }

    private function shouldUseExistingCustomerAddress(): bool
    {
        $id = $this->postCustomerAddressId();
        return $id !== 'new' && is_numeric($id);
    }

    private function getDefaultBillingAddress(): ?AddressInterface
    {
        return $this->customerAddressService->getCurrentCustomerDefaultBillingAddress();
    }

    private function hasDefaultBillingAddress(): bool
    {
        return (bool) $this->getDefaultBillingAddress();
    }
}
