<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

use MageHx\MahxCheckout\Data\Address\CardData;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Model\Quote\Address;
use MageHx\MahxCheckout\Model\QuoteDetails;
use MageHx\MahxCheckout\Service\CustomerAddressService;
use MageHx\MahxCheckout\Service\PrepareAddressLines;

class BillingAddressCards implements ArgumentInterface
{
    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly CustomerAddressService $customerAddressService,
        private readonly PrepareAddressLines $prepareAddressLinesService,
    ) {}

    /**
     * @return array<int, CardData>
     */
    public function getAddressCards(): array
    {
        return array_map(fn ($address) => $this->buildCardData($address), $this->getAllBillingCandidates());
    }

    private function getAllBillingCandidates(): array
    {
        $customerAddresses = $this->customerAddressService->getCurrentCustomerAddressList();
        $billingAddress = $this->quote->getBillingAddress();
        $newAddresses = $this->getNewAddresses();
        $additional = [];

        if (!$billingAddress->getCustomerAddressId() && $billingAddress->validate() === true) {
            $additional[] = $billingAddress;
        }

        if (!empty($newAddresses)) {
            foreach ($newAddresses as $address) {
                if ($address->validate() === true) {
                    $additional[] = $address;
                }
            }
        }

        return [...$customerAddresses, ...$additional];
    }

    private function buildCardData(mixed $address): CardData
    {
        $billingAddress = $this->quote->getBillingAddress();
        $addressLines = $this->prepareAddressLinesService->getLinesOfAddress($address);
        $isNewAddress = $address instanceof Address;
        $addressId = (int)($isNewAddress ? $billingAddress->getId() : $billingAddress->getCustomerAddressId());

        return CardData::from([
            'addressId' => $isNewAddress ? $address->getAddressType() : (string)$address->getId(),
            'isSelected' => (int)$address->getId() === $addressId,
            'addressLines' => $addressLines,
        ]);
    }

    /**
     * @return Address[]
     */
    private function getNewAddresses(): array
    {
        return array_filter(
            $this->quote->getAllAddresses(),
            static fn (Address $address) => $address->getAddressType() === 'new' && $address->validate() === true
        );
    }
}
