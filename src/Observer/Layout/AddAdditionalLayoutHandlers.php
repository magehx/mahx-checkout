<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\Layout;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;

/**
 * This adds special layout handles that can be used when customer is logged in. It is useful to introduce or change
 * blocks which are related to customer logged in scenario.
 *
 * An example, suppose there are layout handles ['default', 'mahxcheckout_some_handle'], then it will add two more
 * handles so that the totals handles looks like: ['default', 'mahxcheckout_some_handle', 'default_customer_logged_in',
 * 'mahxcheckout_some_handle_customer_logged_in']
 *
 * @event layout_load_before
 */
class AddAdditionalLayoutHandlers implements ObserverInterface
{
    public function __construct(
        private CustomerSession $customerSession,
    ) {
    }

    public function execute(Observer $observer): void
    {
        if (!$this->isCustomerLoggedIn() || !$this->isMahxCheckoutAction($observer)) {
            return;
        }

        $this->addAdditionalHandles($observer);
    }

    private function isMahxCheckoutAction(Observer $observer): bool
    {
        $fullAction = (string) $observer->getData('full_action_name');

        return str_contains($fullAction, 'mahxcheckout');
    }

    private function isCustomerLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    private function addAdditionalHandles(Observer $observer): void
    {
        /** @var LayoutInterface $layout */
        $layout = $observer->getData('layout');
        $update = $layout->getUpdate();
        $currentHandles = $update->getHandles();

        foreach ($currentHandles as $handle) {
            $update->addHandle($handle . '_customer_logged_in');
        }
    }
}
