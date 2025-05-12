<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use Magento\Ui\Block\Component\StepsWizard\StepInterface;
use MageHx\MahxCheckout\Data\CheckoutStepData;
use MageHx\MahxCheckout\Model\StepManager\CheckoutStepPool;

class StepValidationService
{
    public function __construct(
        private readonly CheckoutStepPool $checkoutStepPool,
    ) {
    }

    public function getValidStepFor(?string $stepName = null): CheckoutStepData
    {
        $steps = $this->checkoutStepPool->getCheckoutStepsData();

        if ($stepName === null) {
            return $this->checkoutStepPool->getDefaultStep();
        }

        foreach ($steps as $step) {
            if (!$step->isValid) {
                // If a previous step is invalid, then it means cannot load requested step
                return $step;
            }

            if ($step->name === $stepName) {
                // Requested step is reached and everything valid till here
                return $step;
            }
        }

        // Default fallback if step not found
        return $this->checkoutStepPool->getDefaultStep();
    }
}
