<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

use MageHx\MahxCheckout\Model\Theme\ActiveCheckoutThemeResolver;
use MageHx\MahxCheckout\Model\Theme\CheckoutThemeInterface;
use MageHx\MahxCheckout\Service\StepSessionManager;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use MageHx\MahxCheckout\Data\CheckoutStepData;

class StepManager implements ArgumentInterface
{
    private ?CheckoutThemeInterface $checkoutTheme;

    public function __construct(
        private readonly Json $jsonSerializer,
        private readonly CheckoutSession $checkoutSession,
        private readonly StepSessionManager $stepSessionManager,
        private readonly ActiveCheckoutThemeResolver $checkoutThemeResolver,
    ) {
        $this->checkoutTheme = $this->checkoutThemeResolver->resolve();
    }

    /**
     * @return CheckoutStepData[]
     */
    public function getStepsInfo(): array
    {
        return $this->checkoutTheme->getSteps();
    }

    public function getStepsJson(): bool|string
    {
        return $this->jsonSerializer->serialize($this->getStepsInfo());
    }

    public function getCurrentStep(): ?CheckoutStepData
    {
        return $this->stepSessionManager->getStepData() ?? $this->checkoutTheme->getInitialStep();
    }

    public function isCurrentStep(CheckoutStepData $step): bool
    {
        return $step->name === $this->getCurrentStep()->name;
    }

    public function isStepCompleted(CheckoutStepData $step): bool
    {
        return $step->order <= $this->getCurrentStep()->order;
    }

    public function getHtmxIncludesForCurrentStep(): string
    {
        $step = $this->getCurrentStep();

        if (!$step) {
            return '';
        }

        $includes = ['#is-billing-same'];
        foreach ($step->formComponents as $formComponent) {
            $includes[] = "#{$formComponent->name}";
        }

        return implode(',', $includes);
    }

    public function isOnLastStep(): bool
    {
        $step = $this->getCurrentStep();

        return $step && $this->checkoutTheme->isLastStep($step);
    }
}
