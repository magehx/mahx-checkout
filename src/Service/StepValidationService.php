<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use MageHx\MahxCheckout\Model\Theme\ActiveCheckoutThemeResolver;
use MageHx\MahxCheckout\Data\CheckoutStepData;

class StepValidationService
{
    public function __construct(
        private readonly ActiveCheckoutThemeResolver $checkoutThemeResolver,
    ) {
    }

    public function getValidStepFor(?string $stepName = null): CheckoutStepData
    {
        $theme = $this->checkoutThemeResolver->resolve();

        if ($stepName === null) {
            return $theme->getInitialStep();
        }

        foreach ($theme->getSteps() as $step) {
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
        return $theme->getInitialStep();
    }
}
