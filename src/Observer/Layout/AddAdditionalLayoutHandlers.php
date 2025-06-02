<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\Layout;

use MageHx\MahxCheckout\Service\CustomerAddressService;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;

/**
 * Observer that dynamically adds layout handles for MahxCheckout during layout generation.
 *
 * It supports:
 * - Adding layout handles for customer-logged-in states, enabling block customization for logged-in users.
 *
 * @event layout_load_before
 */
class AddAdditionalLayoutHandlers implements ObserverInterface
{
    private ?LayoutInterface $layout = null;

    public function __construct(
        private readonly CustomerSession $customerSession,
        private readonly CustomerAddressService $customerAddressService
    ) {}

    public function execute(Observer $observer): void
    {
        if (!$this->isMahxCheckoutAction($observer) || !$this->isCustomerLoggedIn()) {
            return;
        }

        $this->layout = $observer->getData('layout');

        $this->addCustomerLoggedInHandles();
        $this->addCustomerHasAddressesHandles();
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
     * Adds additional layout handles with `_customer_logged_in` suffix for every existing handle.
     * Enables conditional rendering of blocks for logged-in customers.
     */
    private function addCustomerLoggedInHandles(): void
    {
        foreach ($this->layout->getUpdate()->getHandles() as $handle) {
            $this->addLayoutHandle("{$handle}_customer_logged_in");
        }
    }

    private function isCustomerLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    private function addLayoutHandle(string $handleName): void
    {
        $this->layout->getUpdate()->addHandle($handleName);
    }

    private function addCustomerHasAddressesHandles(): void
    {
        if (!$this->customerAddressService->isCurrentCustomerHoldsAddress()) {
            return;
        }

        foreach ($this->layout->getUpdate()->getHandles() as $handle) {
            $this->addLayoutHandle("{$handle}_customer_has_addresses");
        }
    }
}
