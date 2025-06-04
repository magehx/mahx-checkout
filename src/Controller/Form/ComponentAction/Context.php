<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Controller\Form\ComponentAction;

use MageHx\HtmxActions\Controller\Context\HtmxActionContext;
use MageHx\MahxCheckout\Model\Theme\ActiveCheckoutThemeResolver;
use MageHx\MahxCheckout\Model\Theme\CheckoutThemeInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\Controller\Result\RawFactory;
use MageHx\MahxCheckout\Model\FormDataStorage;
use MageHx\MahxCheckout\Service\HtmxHeaderManager;
use MageHx\MahxCheckout\Service\StepSessionManager;
use MageHx\MahxCheckout\Service\StepValidationService;

class Context
{
    public ?CheckoutThemeInterface $activeTheme = null;

    public function __construct(
        public readonly HtmxActionContext $htmxActionContext,
        public readonly RawFactory $rawFactory,
        public readonly LayoutFactory $layoutFactory,
        public readonly FormDataStorage $formDataStorage,
        public readonly CheckoutSession $checkoutSession,
        public readonly HtmxHeaderManager $htmxHeaderManager,
        public readonly StepSessionManager $stepSessionManager,
        public readonly StepValidationService $stepValidationService,
        private readonly ActiveCheckoutThemeResolver $activeCheckoutThemeResolver,
    ) {
        $this->activeTheme = $this->activeCheckoutThemeResolver->resolve();
    }
}
