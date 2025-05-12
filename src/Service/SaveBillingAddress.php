<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory as QuoteAddressInterfaceFactory;
use Magento\Quote\Model\ResourceModel\Quote\Address as QuoteAddressResource;
use Magento\Quote\Model\ResourceModel\Quote\AddressFactory as QuoteAddressResourceFactory;
use MageHx\MahxCheckout\Data\AddressData;
use MageHx\MahxCheckout\Model\QuoteDetails;

class SaveBillingAddress
{
    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly CartRepositoryInterface $quoteRepository,
        private readonly QuoteAddressInterfaceFactory $quoteAddressFactory,
        private readonly QuoteAddressResourceFactory $quoteAddressResourceFactory,
        private readonly BillingAddressManagementInterface $billingAddressManagement,
    ) {
    }

    public function execute(AddressData $billingAddressData): void
    {
        if ($billingAddressData->same_as_billing) {
            $this->setShippingAddressAsBilling();
        } else {
            $this->saveBillingAddress($billingAddressData);
        }

        $this->updateShippingAddress($billingAddressData);
        $this->recalculateTotals();
    }

    private function setShippingAddressAsBilling(): void
    {
        $shippingAddress = $this->quote->getShippingAddress();

        // Clone shipping address for billing
        $billingAddress = clone $shippingAddress;
        $billingAddress->setAddressType('billing');
        $billingAddress->setSameAsBilling(true);

        $this->billingAddressManagement->assign($this->quote->getId(), $billingAddress);
    }

    private function saveBillingAddress(AddressData $billingAddressData): void
    {
        /** @var AddressInterface $address */
        $address = $this->quoteAddressFactory->create();
        $address->setData($billingAddressData->toArray());
        $this->billingAddressManagement->assign($this->quote->getId(), $address);
    }

    private function updateShippingAddress(AddressData $billingAddressData): void
    {
        $shippingAddress = $this->quote->getShippingAddress();
        // need to make sure shipping address same_as_billing always sync to the correct value.
        if ((bool)$shippingAddress->getSameAsBilling() !== $billingAddressData->same_as_billing) {
            /** @var QuoteAddressResource $quoteAddressResourceModel */
            $quoteAddressResourceModel = $this->quoteAddressResourceFactory->create();
            $shippingAddress->setSameAsBilling($billingAddressData->same_as_billing);
            $quoteAddressResourceModel->save($shippingAddress);
        }
    }

    private function recalculateTotals(): void
    {
        $quote = $this->quote->getInstance();
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
    }
}
