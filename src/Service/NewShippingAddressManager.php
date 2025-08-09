<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use MageHx\MahxCheckout\Data\AddressData;
use MageHx\MahxCheckout\Data\ShippingInformation;
use MageHx\MahxCheckout\Model\QuoteDetails;

class NewShippingAddressManager
{
    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly CheckoutSession $checkoutSession,
        private readonly CartRepositoryInterface $quoteRepository,
    ) {
    }

    public function save(AddressData $addressData): void
    {
        $quote = $this->quote->getInstance();
        $shippingAddress = $quote->getShippingAddress();

        $shippingAddress->addData($addressData->getAddress());
        $shippingAddress->setCustomerAddressId(null);
        $shippingAddress->setRegionId(null);
        $shippingAddress->setCollectShippingRates(true);

        $quote->setShippingAddress($shippingAddress);
        $this->quoteRepository->save($quote);

        $this->checkoutSession->replaceQuote($quote);
    }

    public function keepShippingAddressAsNew(ShippingInformation $shippingInformation): void
    {
        if (!$shippingInformation->address->customer_address_id) {
            return;
        }

        $shippingAddress = $this->quote->getShippingAddress();

        if ($shippingAddress->getCustomerAddressId()) {
            return;
        }

        $newAddress = $shippingAddress;

        foreach ($this->quote->getAllAddresses() as $address) {
            if ($address->getAddressType() === 'new') {
                $addressId = $address->getId();
                $newAddress = $address;
                $newAddress->setData($shippingAddress->getData());
                $newAddress->setId($addressId);
                break;
            }
        }

        $newAddress->setAddressType('new')->save();
    }

    public function getNewAddress(): ?QuoteAddress
    {
        $shippingAddress = $this->quote->getShippingAddress();

        if (!$shippingAddress->getCustomerAddressId()) {
            return $shippingAddress;
        }

        foreach ($this->quote->getAllAddresses() as $address) {
            if ($address->getAddressType() === 'new') {
                return $address;
            }
        }

        return null;
    }
}
