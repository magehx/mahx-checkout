<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use MageHx\MahxCheckout\Data\CheckoutStepData;
use MageHx\MahxCheckout\Model\StepManager\CheckoutStepPool;

class StepManager implements ArgumentInterface
{
    public function __construct(
        private readonly Json $jsonSerializer,
        private readonly CheckoutStepPool $checkoutStepPool,
        private readonly CheckoutSession $checkoutSession,
    ) {
    }

    /**
     * @return CheckoutStepData[]
     */
    public function getStepsInfo(): array
    {
        return $this->checkoutStepPool->getCheckoutStepsData();
    }

    public function getStepsJson(): bool|string
    {
        return $this->jsonSerializer->serialize($this->checkoutStepPool->getCheckoutStepsData());
    }

    public function getCurrentStep(): ?CheckoutStepData
    {
        return $this->checkoutSession->getMahxCheckoutCurrentStep();
    }

    public function getHtmxIncludesForCurrentStep(): string
    {
        $step = $this->getCurrentStep();

        if (!$step) {
            return '';
        }

        $includes = ['#current-step'];
        foreach ($step->formComponents as $formComponent) {
            $includes[] = "#{$formComponent->name}";
        }

        return implode(',', $includes);
    }

    public function isOnLastStep(): bool
    {
        $step = $this->getCurrentStep();

        return $step && $this->checkoutStepPool->isLastStep($step);
    }
}
