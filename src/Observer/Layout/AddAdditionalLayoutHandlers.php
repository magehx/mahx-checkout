<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\Layout;

use MageHx\MahxCheckout\Model\Theme\ActiveCheckoutThemeResolver;
use MageHx\MahxCheckout\Service\CustomerAddressService;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;

/**
 * Adds additional layout handles dynamically when MAHX Checkout is being rendered.
 *
 * Triggered on `layout_load_before`, it adds:
 * - A handle based on the active checkout theme (e.g. `mahxcheckout_default`)
 * - A `_customer_logged_in` suffix for all handles if customer is logged in
 * - A `_customer_has_addresses` suffix if logged-in customer has saved addresses
 *
 * These layout handles allow conditional block rendering based on checkout context.
 */
class AddAdditionalLayoutHandlers implements ObserverInterface
{
    private ?LayoutInterface $layout = null;

    public function __construct(
        private readonly CustomerSession $customerSession,
        private readonly ActiveCheckoutThemeResolver $activeThemeResolver,
        private readonly CustomerAddressService $customerAddressService,
    ) {}

    public function execute(Observer $observer): void
    {
        if (!$this->isMahxCheckoutAction($observer)) {
            return;
        }

        $this->layout = $observer->getData('layout');

        $this->addThemeLayoutHandles();

        if ($this->isCustomerLoggedIn()) {
            $this->addCustomerLoggedInHandles();
            $this->addCustomerHasAddressesHandles();
        }
    }

    /**
     * Returns true if the full action name indicates a MahxCheckout route.
     */
    private function isMahxCheckoutAction(Observer $observer): bool
    {
        $fullAction = (string) $observer->getData('full_action_name');
        return str_contains($fullAction, 'mahxcheckout');
    }

    /**
     * Adds theme-based layout handles such as:
     * - mahxcheckout_{themeCode}
     * - mahxcheckout_{themeCode}_layout
     */
    private function addThemeLayoutHandles(): void
    {
        $themeCode = $this->activeThemeResolver->resolve()->getCode();

        $this->addLayoutHandle("mahxcheckout_{$themeCode}");
        $this->addLayoutHandle("mahxcheckout_{$themeCode}_layout");
    }

    /**
     * Adds additional handles with `_customer_logged_in` suffix for each existing handle.
     */
    private function addCustomerLoggedInHandles(): void
    {
        foreach ($this->layout->getUpdate()->getHandles() as $handle) {
            $this->addLayoutHandle("{$handle}_customer_logged_in");
        }
    }

    /**
     * Adds additional handles with `_customer_has_addresses` suffix
     * if customer has one or more saved addresses.
     */
    private function addCustomerHasAddressesHandles(): void
    {
        if (!$this->customerAddressService->isCurrentCustomerHoldsAddress()) {
            return;
        }

        foreach ($this->layout->getUpdate()->getHandles() as $handle) {
            $this->addLayoutHandle("{$handle}_customer_has_addresses");
        }
    }

    /**
     * Determines whether a customer is logged in.
     */
    private function isCustomerLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * Adds a single layout handle.
     */
    private function addLayoutHandle(string $handleName): void
    {
        $this->layout->getUpdate()->addHandle($handleName);
    }
}
