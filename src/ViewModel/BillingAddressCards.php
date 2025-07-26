<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

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

    public function getAddressCards(): array
    {
        return array_map(fn ($address) => $this->buildCardData($address), $this->getAllBillingCandidates());
    }

    private function getAllBillingCandidates(): array
    {
        $customerAddresses = $this->customerAddressService->getCurrentCustomerAddressList();
        $billingAddress = $this->quote->getBillingAddress();
        $shippingAddress = $this->quote->getShippingAddress();

        $additional = [];

        if (!$shippingAddress->getCustomerAddressId() && !$shippingAddress->getSameAsBilling()) {
            $additional[] = $shippingAddress;
        }

        if (!$billingAddress->getCustomerAddressId()) {
            $additional[] = $billingAddress;
        }

        return [...$customerAddresses, ...$additional];
    }

    private function buildCardData(mixed $address): array
    {
        $billingAddress = $this->quote->getBillingAddress();
        $addressLines = $this->prepareAddressLinesService->getLinesOfAddress($address);
        $isNewAddress = $address instanceof Address;
        $addressId = (int)($isNewAddress ? $billingAddress->getId() : $billingAddress->getCustomerAddressId());

        $label = sprintf(
            '%s, %s, %s %s, %s, %s: %s',
            $addressLines['name'],
            $addressLines['line_1'],
            $addressLines['line_2'],
            $addressLines['postcode'],
            $addressLines['country'],
            __('Phone'),
            $addressLines['telephone']
        );

        return [
            'id' => $isNewAddress ? $address->getAddressType() : (string)$address->getId(),
            'isSelected' => (int)$address->getId() === $addressId,
            'label' => $label,
        ];
    }
}
