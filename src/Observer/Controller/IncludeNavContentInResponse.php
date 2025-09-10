<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\Controller;

use MageHx\MahxCheckout\Model\CheckoutDataStorage;
use MageHx\MahxCheckout\Model\Theme\ActiveCheckoutThemeResolver;
use MageHx\MahxCheckout\Model\Theme\CheckoutThemeInterface;
use MageHx\MahxCheckout\Service\GenerateBlockHtml;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class IncludeNavContentInResponse implements ObserverInterface
{
    private ?Observer $observer = null;

    public function __construct(
        private readonly GenerateBlockHtml $generateBlockHtml,
        private readonly CheckoutDataStorage $checkoutDataStorage,
    ) {
    }


    public function execute(Observer $observer): void
    {
        $this->observer = $observer;

        if (! $this->isAllowedActions() || $this->checkoutDataStorage->isErrorResponse()) {
            return;
        }

        $this->getTransport()->setData('additional_html', $this->withComponentHtml('checkout.step.navigation'));
    }

    private function isAllowedActions(): bool
    {
        $actionName = $this->getTransport()->getData('full_action_name');

        return in_array($actionName, [
            'mahxcheckout_shipping_saveShippingInformation',
            'mahxcheckout_step_getStepContent'
        ]);
    }

    protected function getTransport(): DataObject
    {
        return $this->observer->getData('transport');
    }

    public function withComponentHtml(string $componentName, bool $withHtmxOob = true): string
    {
        $componentHtml = $this->generateBlockHtml->getComponentHtml($componentName, $withHtmxOob);

        return $this->getTransport()->getData('additional_html') . $componentHtml;
    }
}
