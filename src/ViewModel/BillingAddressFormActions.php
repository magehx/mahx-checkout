<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

use MageHx\MahxCheckout\Model\QuoteDetails;
use MageHx\MahxCheckout\Service\CustomerAddressService;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class BillingAddressFormActions implements ArgumentInterface
{
    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly CustomerAddressService $customerAddressService,
    ) {
    }

    public function isCustomerHaveAddresses(): bool
    {
        return $this->customerAddressService->isCurrentCustomerHoldsAddress();
    }

    public function canShow(): bool
    {
        return !$this->quote->isVirtualQuote();
    }

    public function isBillingSame(): bool
    {
        return $this->quote->isBillingSameAsShipping();
    }
}
