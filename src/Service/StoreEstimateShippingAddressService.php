<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use MageHx\MahxCheckout\Data\ShippingEstimateFieldsData;

class StoreEstimateShippingAddressService
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
    ) {
    }

    public function execute(int $cartId, ShippingEstimateFieldsData $addressData): void
    {
        /** @var Quote $quote */
        $quote = $this->cartRepository->get($cartId);
        $shippingAddress = $quote->getShippingAddress();

        $shippingAddress->setPostcode($addressData->postcode);
        $shippingAddress->setCountryId($addressData->country);
        $shippingAddress->setRegion($addressData->region);

        // Disable validation on save
        $shippingAddress->setShouldIgnoreValidation(true);

        $this->cartRepository->save($quote);
    }
}
