<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\StepManager;

use MageHx\MahxCheckout\Model\Theme\CheckoutThemeInterface;
use MageHx\MahxCheckout\Model\Theme\CheckoutThemeRegistry;
use Magento\Framework\UrlInterface;
use MageHx\MahxCheckout\Data\CheckoutStepData;
use MageHx\MahxCheckout\Data\FormComponentData;
use MageHx\MahxCheckout\Model\EventDispatcher;

class CheckoutStepPool
{
    /**
     * @var array<string, CheckoutStepInterface[]>
     */
    private array $themeSteps;

    /**
     * @var CheckoutStepInterface[]
     */
    private array $steps = [];

    /** @var ?CheckoutStepData[]  */
    private ?array $checkoutStepsData = null;

    public function __construct(
        private readonly UrlInterface $urlBuilder,
        private readonly EventDispatcher $eventDispatcher,
        array $themeSteps = []
    ) {
        $this->themeSteps = $themeSteps;
    }

    public function loadStepsForTheme(CheckoutThemeInterface $theme): void
    {
        foreach (array_reverse([$theme, ...$theme->getParentThemes()]) as $stepTheme) {
            $this->steps = array_merge($this->steps, $this->themeSteps[$stepTheme->getCode()] ?? []);
        }

        $this->prepareCheckoutStepData();
    }

    /**
     * @return CheckoutStepInterface[]
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    /**
     * @return CheckoutStepData[]
     */
    public function getCheckoutStepsData(): array
    {
        return $this->checkoutStepsData ?? [];
    }

    private function prepareCheckoutStepData(): void
    {
        $transportSteps = $this->eventDispatcher->dispatchStepsDataBuildBefore(['steps' => $this->steps]);
        $this->steps = $transportSteps->getData('steps');

        $checkoutStepsData = [];
        $order = 1;

        foreach ($this->steps as $step) {
            $formComponents = [];
            foreach ($step->getFormComponents() as $component) {
                $formComponents[$component->getName()] = FormComponentData::from([
                    'name' => $component->getName(),
                    'label' => $component->getLabel(),
                ]);
            }

            $checkoutStepsData[$step->getName()] = CheckoutStepData::from([
                'order' => $order++,
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

        $transportStepsData = $this->eventDispatcher
            ->dispatchStepsDataBuildAfter(['checkout_steps_data' => $checkoutStepsData]);

        $this->checkoutStepsData = $transportStepsData->getData('checkout_steps_data');
        uasort($this->checkoutStepsData, fn($a, $b) => $a->order <=> $b->order);
    }
}
