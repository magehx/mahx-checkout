<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Controller\Form\ComponentAction;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\Controller\Result\RawFactory;
use MageHx\MahxCheckout\Model\FormDataStorage;
use MageHx\MahxCheckout\Model\StepManager\CheckoutStepPool;
use MageHx\MahxCheckout\Service\HtmxHeaderManager;
use MageHx\MahxCheckout\Service\StepSessionManager;
use MageHx\MahxCheckout\Service\StepValidationService;

class Context
{
    public function __construct(
        public readonly \Magento\Framework\App\Action\Context $magentoAppActionContext,
        public readonly RawFactory $rawFactory,
        public readonly LayoutFactory $layoutFactory,
        public readonly FormDataStorage $formDataStorage,
        public readonly CheckoutSession $checkoutSession,
        public readonly CheckoutStepPool $checkoutStepPool,
        public readonly HtmxHeaderManager $htmxHeaderManager,
        public readonly StepSessionManager $stepSessionManager,
        public readonly StepValidationService $stepValidationService,
    ) {
    }
}
