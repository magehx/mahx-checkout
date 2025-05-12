<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use MageHx\MahxCheckout\Model\FormDataStorage;
use MageHx\MahxCheckout\Model\QuoteDetails;

class GuestEmail implements ArgumentInterface
{
    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly FormDataStorage $formDataStorage,
    ) {
    }

    public function getEmail(): string
    {
        return $this->formDataStorage->getData('email') ?? $this->quote->getQuoteCustomerEmail();
    }
}
