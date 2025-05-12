<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Plugin\Model\StepManagerCheckoutStepInterface;

use MageHx\MahxCheckout\Model\QuoteDetails;
use MageHx\MahxCheckout\Model\StepManager\CheckoutStepInterface;

class ApplyStepValidation
{
    public function __construct(
        private readonly QuoteDetails $quote
    ) {
    }

    public function afterIsValid(CheckoutStepInterface $subject, bool $result): bool
    {
        return match($subject->getName()) {
            'shipping' => $this->isShippingStepValid(),
            'payment' => $this->isPaymentStepValid(),
            default => $result,
        };
    }

    private function isShippingStepValid(): bool
    {
        return $this->quote->getShippingAddress()->validate() === true && $this->quote->getShippingMethod();
    }

    private function isPaymentStepValid(): bool
    {
        return $this->quote->getBillingAddress()->validate() === true && $this->quote->getPaymentMethod()?->getMethod();
    }
}
