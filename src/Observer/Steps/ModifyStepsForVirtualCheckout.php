<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\Steps;

use MageHx\MahxCheckout\Model\QuoteDetails;
use MageHx\MahxCheckout\Model\StepManager\CheckoutStepInterface;
use MageHx\MahxCheckout\Model\StepManager\FormComponentInterfaceFactory as StepFormComponentFactory;
use MageHx\MahxCheckout\Model\Theme\ActiveCheckoutThemeResolver;
use MageHx\MahxCheckout\Model\Theme\CheckoutThemeInterface;
use MageHx\MahxCheckout\Model\Theme\DefaultCheckoutTheme;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * For virtual only checkout flow, we don't need shipping step. Only payment step is required.
 *
 * @event mahxcheckout_steps_data_build_before
 */
class ModifyStepsForVirtualCheckout implements ObserverInterface
{
    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly ActiveCheckoutThemeResolver $checkoutThemeResolver,
        private readonly StepFormComponentFactory $stepFormComponentFactory,
    ) {
    }

    public function execute(Observer $observer): void
    {
        $theme = $observer->getData('transport')->getData('theme');

        if (!$this->quote->isVirtualQuote() || !$this->belongToDefaultTheme($theme)) {
            return;
        }

        /** @var CheckoutStepInterface [] $steps */
        $steps = $observer->getData('transport')->getData('steps');

        unset($steps['shipping']);
        $steps['payment']->setIsDefault(true)->setFormComponents($this->prepareFormComponents($steps));

        $observer->getData('transport')->setData('steps', $steps);
    }

    private function prepareFormComponents(array $steps): array
    {
        $guestEmailForm = $this->stepFormComponentFactory->create([
            'name' => 'guest-email-form',
            'label' => 'Guest Email Info'
        ]);

        return [$guestEmailForm, ...$steps['payment']->getFormComponents()];
    }

    private function belongToDefaultTheme(CheckoutThemeInterface $stepTheme): bool
    {
        if ($stepTheme->getCode() === DefaultCheckoutTheme::THEME_CODE) {
            return true;
        }

        $parentThemeCodes = array_map(
            static fn (CheckoutThemeInterface $theme) => $theme->getCode(),
            $stepTheme->getParentThemes()
        );

        return in_array(DefaultCheckoutTheme::THEME_CODE, $parentThemeCodes);
    }
}
