<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\Layout;

use MageHx\MahxCheckout\Model\QuoteDetails;
use MageHx\MahxCheckout\Model\Theme\ActiveCheckoutThemeResolver;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;

/**
 * Observer that dynamically adds layout handles for MahxCheckout during layout generation.
 *
 * - Adding a special layout handle for virtual carts to hide shipping-related blocks and insert billing-only blocks.
 *
 * @event layout_load_before
 */
class AddVirtualLayoutHandler implements ObserverInterface
{
    private ?LayoutInterface $layout = null;

    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly ActiveCheckoutThemeResolver $activeThemeResolver,
    ) {}

    public function execute(Observer $observer): void
    {
        if (!$this->isMahxCheckoutAction($observer) || !$this->quote->getInstance()->isVirtual()) {
            return;
        }

        $this->layout = $observer->getData('layout');

        $this->addVirtualCheckoutHandle();
    }

    /**
     * Determines if the current action is part of MahxCheckout routes.
     */
    private function isMahxCheckoutAction(Observer $observer): bool
    {
        $fullAction = (string) $observer->getData('full_action_name');

        return str_contains($fullAction, 'mahxcheckout');
    }

    /**
     * Adds a specific layout handle for virtual checkout flows.
     * Use `mahxcheckout_<themecode>_virtual.xml` to remove shipping blocks and add billing-only logic.
     */
    private function addVirtualCheckoutHandle(): void
    {
        $theme = $this->activeThemeResolver->resolve();
        $this->layout->getUpdate()->addHandle("mahxcheckout_{$theme->getCode()}_virtual");
    }
}
