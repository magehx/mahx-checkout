<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

use MageHx\MahxCheckout\Data\Address\AddressLinesData;
use MageHx\MahxCheckout\Data\Address\CardData;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Model\Quote\Address;
use MageHx\MahxCheckout\Model\QuoteDetails;
use MageHx\MahxCheckout\Service\CustomerAddressService;
use MageHx\MahxCheckout\Service\NewShippingAddressManager;
use MageHx\MahxCheckout\Service\PrepareAddressLines;

class ShippingAddressCards implements ArgumentInterface
{
    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly UrlInterface $urlBuilder,
        private readonly CustomerAddressService $customerAddressService,
        private readonly PrepareAddressLines $prepareAddressLinesService,
        private readonly NewShippingAddressManager $newShippingAddressManager,
    ) {}

    public function getEstimateShippingByAddressIdUrl(string $addressId): string
    {
        if ($addressId === 'new') {
            return $this->urlBuilder->getUrl("mahxcheckout/form/estimateShippingMethods");
        }

        return $this->urlBuilder->getUrl(
            "mahxcheckout/form/estimateShippingMethodsByAddressId",
            ['address_id' => $addressId]
        );
    }

    public function getNewAddressEstimationProps(): array
    {
        $newAddress = $this->newShippingAddressManager->getNewAddress();

        if (!$newAddress) {
            return [];
        }

        return [
            'postcode' => $newAddress->getPostcode(),
            'country_id' => $newAddress->getCountryId(),
            'region' => $newAddress->getRegionId() ?: $newAddress->getRegion(),
        ];
    }

    public function getNewAddressEstimatePropsJson(): string
    {
        return json_encode($this->getNewAddressEstimationProps());
    }

    public function isNewAddressExists(): bool
    {
        $newAddress = $this->newShippingAddressManager->getNewAddress();

        return $newAddress && $newAddress->validate() === true;
    }

    public function isCurrentCustomerHaveAddresses(): bool
    {
        return $this->customerAddressService->isCurrentCustomerHoldsAddress();
    }

    public function getShippingAddressLines(): AddressLinesData
    {
        return AddressLinesData::from($this->prepareAddressLinesService->getLinesOfAddress(
            $this->quote->getShippingAddress()
        ));
    }

    /**
     * @return array<int|string, CardData>
     */
    public function getCustomerAddressCards(): array
    {
        $shippingAddress = $this->quote->getShippingAddress();
        $customerAddresses = $this->customerAddressService->getCurrentCustomerAddressList();
        $allAddresses = $this->buildAddressList($customerAddresses);
        $selectedAddressId = (int)$shippingAddress->getId();
        $cards = [];

        foreach ($allAddresses as $address) {
            $isNew = $address instanceof Address;
            $addressId = $isNew ? 'new' : (string) $address->getId();

            $cards[$addressId] = CardData::from([
                'addressId' => $addressId,
                'isSelected' => $selectedAddressId === (int)$address->getId(),
                'addressLines' => $this->prepareAddressLinesService->getLinesOfAddress($address),
            ]);
        }

        return $cards;
    }

    public function getCustomerAddressSelected(): string
    {
        $customerAddressId = (string)$this->quote->getShippingAddress()->getCustomerAddressId();

        if ($customerAddressId) {
            return $customerAddressId;
        }

        if ($this->newShippingAddressManager->getNewAddress()->validate() !== true) {
            return (string)$this->customerAddressService->getCurrentCustomerDefaultShippingAddress()?->getId() ?? 'new';
        }

        return 'new';
    }

    /**
     * Adds the shipping address to the customer address list if it's a "new" address.
     */
    private function buildAddressList(array $customerAddresses): array
    {
        $newAddress = $this->newShippingAddressManager->getNewAddress();
        if ($newAddress && $newAddress->validate() === true) {
            return [...$customerAddresses, $newAddress];
        }

        return $customerAddresses;
    }
}
