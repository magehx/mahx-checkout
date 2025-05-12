<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\StepManager;

use Magento\Framework\UrlInterface;
use MageHx\MahxCheckout\Data\CheckoutStepData;
use MageHx\MahxCheckout\Data\FormComponentData;
use MageHx\MahxCheckout\Model\EventDispatcher;

class CheckoutStepPool
{
    /**
     * @var CheckoutStepInterface[]
     */
    private array $steps;
    private ?array $checkoutStepsData = null;

    public function __construct(
        private readonly UrlInterface $urlBuilder,
        private readonly EventDispatcher $eventDispatcher,
        array $steps = []
    ) {
        $this->steps = $steps;
        $this->prepareCheckoutStepData();
    }

    /**
     * @return CheckoutStepData[]
     */
    public function getCheckoutStepsData(): array
    {
        return $this->checkoutStepsData;
    }

    /**
     * Get the default step (the one marked with isDefault = true).
     */
    public function getDefaultStep(): ?CheckoutStepData
    {
        /** @var CheckoutStepData $step */
        foreach ($this->checkoutStepsData as $step) {
            if ($step->isDefault) {
                return $step;
            }
        }

        return null;
    }

    public function getStepByName(string $stepName): ?CheckoutStepData
    {
        foreach ($this->checkoutStepsData ?? [] as $step) {
            if ($step->name === $stepName) {
                return $step;
            }
        }

        return null;
    }

    public function getNextStepOf(string|CheckoutStepData $stepToCheck): ?CheckoutStepData
    {
        $flag = false;

        foreach ($this->checkoutStepsData as $step) {
            if ($stepToCheck === $step || $step->name === $stepToCheck) {
                $flag = true;
                continue;
            }

            if ($flag) {
                return $step;
            }
        }

        return null;
    }

    public function isLastStep(string|CheckoutStepData $stepToCheck): bool
    {
        $lastStep = end($this->checkoutStepsData);
        if (is_string($stepToCheck)) {
            return $stepToCheck === $lastStep->name;
        }

        return $stepToCheck->name === $lastStep->name;
    }

    /**
     * Build a CheckoutStepData instance from a CheckoutStepInterface.
     */
    private function buildStepData(CheckoutStepInterface $step, int $order): CheckoutStepData
    {
        $formComponents = [];

        foreach ($step->getFormComponents() as $component) {
            $formComponents[$component->getName()] = FormComponentData::from([
                'name' => $component->getName(),
                'label' => $component->getLabel(),
            ]);
        }

        return CheckoutStepData::from([
            'order' => $order,
            'name' => $step->getName(),
            'label' => $step->getLabel(),
            'urlHash' => $step->getUrlHash(),
            'isDefault' => $step->isDefault(),
            'isValid' => $step->isValid(),
            'buttonLabel' => $step->getButtonLabel(),
            'layoutHandle' => $step->getLayoutHandle(),
            'saveDataUrl' => $this->urlBuilder->getUrl($step->getSaveDataUrl()),
            'formComponents' => $formComponents,
        ]);
    }

    private function prepareCheckoutStepData(): void
    {
        $transportSteps = $this->eventDispatcher->dispatchStepsDataBuildBefore([ 'steps' => $this->steps ]);
        $this->steps = $transportSteps->getData('steps');
        $checkoutStepsData = [];
        $order = 1;

        foreach ($this->steps as $step) {
            $stepName = $step->getName();
            $checkoutStepsData[$stepName] = $this->buildStepData($step, $order++);
        }

        $transportStepsData = $this->eventDispatcher
            ->dispatchStepsDataBuildAfter([ 'checkout_steps_data' => $checkoutStepsData ]);
        $this->checkoutStepsData = $transportStepsData->getData('checkout_steps_data');
        uasort($this->checkoutStepsData, fn ($a, $b) => $a->order <=> $b->order);
    }
}
