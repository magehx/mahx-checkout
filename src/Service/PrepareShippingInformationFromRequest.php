<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\RequestInterface;
use MageHx\MahxCheckout\Data\ShippingInformation;
use MageHx\MahxCheckout\Model\QuoteDetails;

class PrepareShippingInformationFromRequest
{
    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly CustomerSession $customerSession,
        private readonly CustomerAddressService $customerAddressService,
        private readonly NewShippingAddressManager $newShippingAddressManager,
    ) {}

    public function execute(array $postData): ShippingInformation
    {
        $addressId = $postData['customer_address_id'] ?? null;
        $isNewAddress = $addressId === 'new';

        if ($isNewAddress || !$this->customerSession->isLoggedIn()) {
            return $this->prepareFromGuestOrNewAddress((array)$postData, $isNewAddress);
        }

        return $this->prepareFromExistingCustomerAddress((int)$addressId, $postData['method'] ?? null);
    }

    private function prepareFromGuestOrNewAddress(array $postData, bool $isNewAddress): ShippingInformation
    {
        $isLoggedIn = $this->customerSession->isLoggedIn();
        $shippingAddress = $this->quote->getShippingAddress();
        $data = $isNewAddress ? $this->newShippingAddressManager->getNewAddress() : $postData;
        $street = $isNewAddress ? $shippingAddress->getStreet() : ($postData['street'] ?? []);
        $region = $isNewAddress ? ($data['region_id'] ?? $data['region'] ?? '') : ($data['region'] ?? '');
        $isBillingSame = !$isLoggedIn || !$this->customerAddressService->getCurrentCustomerDefaultBillingAddress();

        return ShippingInformation::from([
            'method' => $postData['method'] ?? '',
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

    private function prepareFromExistingCustomerAddress(int $addressId, ?string $method): ShippingInformation
    {
        $address = $this->customerAddressService->getCurrentCustomerAddressById($addressId);
        $defaultBilling = $this->customerAddressService->getCurrentCustomerDefaultBillingAddress();

        $sameAsBilling = $defaultBilling && ((int)$address->getId() === (int)$defaultBilling->getId());

        return ShippingInformation::from([
            'firstname' => $address->getFirstname(),
            'lastname' => $address->getLastname(),
            'street' => $address->getStreet(),
            'city' => $address->getCity(),
            'country_id' => $address->getCountryId(),
            'postcode' => $address->getPostcode(),
            'telephone' => $address->getTelephone(),
            'region' => $address->getRegion()->getRegion(),
            'method' => $method ?? '',
            'customer_id' => (int)$this->customerSession->getCustomer()->getId(),
            'customer_address_id' => $addressId,
            'same_as_billing' => $sameAsBilling,
        ]);
    }
}
