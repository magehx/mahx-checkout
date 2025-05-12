<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use Magento\Checkout\Model\Session as CheckoutSession;
use MageHx\MahxCheckout\Data\CheckoutStepData;

class StepSessionManager
{
    public function __construct(
        private readonly CheckoutSession $checkoutSession,
    ) {
    }

    public function getStepData(): ?CheckoutStepData
    {
        return $this->checkoutSession->getMahxCheckoutCurrentStep();
    }

    public function setStepData(CheckoutStepData $step): void
    {
        $this->checkoutSession->setMahxCheckoutCurrentStep($step);
    }
}
