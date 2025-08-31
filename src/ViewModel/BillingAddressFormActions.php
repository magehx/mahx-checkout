<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

use MageHx\MahxCheckout\Model\QuoteDetails;
use MageHx\MahxCheckout\Service\CustomerAddressService;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class BillingAddressFormActions implements ArgumentInterface
{
    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly CustomerSession $customerSession,
        private readonly CustomerAddressService $customerAddressService,
    ) {
    }

    public function isCustomerHaveAddresses(): bool
    {
        return $this->customerAddressService->isCurrentCustomerHoldsAddress();
    }

    public function isVirtualQuote(): bool
    {
        return $this->quote->isVirtualQuote();
    }

    public function isBillingSame(): bool
    {
        return !$this->isVirtualQuote() && $this->quote->isBillingSameAsShipping();
    }

    public function isCustomerLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    public function canShowCancelButton(): bool
    {
        if (!$this->isVirtualQuote()) {
            return true;
        }

        return $this->isCustomerLoggedIn() && $this->isCustomerHaveAddresses();
    }
}
