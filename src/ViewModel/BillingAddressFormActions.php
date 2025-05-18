<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

use MageHx\MahxCheckout\Model\QuoteDetails;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class BillingAddressFormActions implements ArgumentInterface
{
    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly CustomerSession $customerSession,
    ) {
    }

    public function isCustomerLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    public function canShow(): bool
    {
        return !$this->quote->isVirtualQuote();
    }
}
