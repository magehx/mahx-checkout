<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\Layout;

use MageHx\MahxCheckout\Model\Theme\ActiveCheckoutThemeResolver;
use MageHx\MahxCheckout\Model\Theme\CheckoutThemeInterface;
use MageHx\MahxCheckout\Service\CurrentDesignTheme;
use MageHx\MahxCheckout\Service\CustomerAddressService;
use MageHx\MahxCheckout\Service\StepSessionManager;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;

/**
 * Adds additional layout handles dynamically when MAHX Checkout is being rendered.
 *
 * Triggered on `layout_load_before`, it adds:
 * - A handle based on the active checkout theme (e.g. `mahxcheckout_theme_default`)
 * - A `_customer_logged_in` suffix for all handles if customer is logged in
 * - A `_customer_has_addresses` suffix if logged-in customer has saved addresses
 *
 * These layout handles allow conditional block rendering based on checkout context.
 */
class AddAdditionalLayoutHandlers implements ObserverInterface
{
    private ?LayoutInterface $layout = null;
    private ?CheckoutThemeInterface $activeTheme = null;

    public function __construct(
        private readonly CustomerSession $customerSession,
        private readonly CurrentDesignTheme $currentTheme,
        private readonly StepSessionManager $stepSessionManager,
        private readonly CustomerAddressService $customerAddressService,
        private readonly ActiveCheckoutThemeResolver $activeThemeResolver,
    ) {}

    public function execute(Observer $observer): void
    {
        if (!$this->isMahxCheckoutAction($observer)) {
            return;
        }

        $this->activeTheme = $this->activeThemeResolver->resolve();
        $this->layout = $observer->getData('layout');

        $this->addCheckoutLayoutHandle();
        $this->addThemeLayoutHandles();
        $this->addCurrentStepLayoutHandle();

        $handles = $this->layout->getUpdate()->getHandles();

        if ($this->isCustomerLoggedIn()) {
            $this->addCustomerLoggedInHandles($handles);
            $this->addCustomerHasAddressesHandles($handles);
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
     * - mahxcheckout_theme_{themeCode}
     */
    private function addThemeLayoutHandles(): void
    {
        foreach (array_reverse([$this->activeTheme, ...$this->activeTheme->getParentThemes()]) as $theme) {
            $handle = "mahxcheckout_theme_{$theme->getCode()}";

            $this->addLayoutHandle($handle);
            $this->addHyvaLayoutHandle($handle);
        }
    }

    /**
     * Adds additional handles with `_customer_logged_in` suffix for each existing handle.
     */
    private function addCustomerLoggedInHandles(array $handles): void
    {
        foreach ($handles as $handle) {
            $this->addLayoutHandle("{$handle}_customer_logged_in");
        }
    }

    /**
     * Adds additional handles with `_customer_has_addresses` suffix
     * if customer has one or more saved addresses.
     */
    private function addCustomerHasAddressesHandles(array $handles): void
    {
        if (!$this->customerAddressService->isCurrentCustomerHoldsAddress()) {
            return;
        }

        foreach ($handles as $handle) {
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

    private function addCheckoutLayoutHandle(): void
    {
        $handle = 'mahxcheckout_layout';
        $this->addLayoutHandle($handle);
        $this->addHyvaLayoutHandle($handle);
    }

    private function addCurrentStepLayoutHandle(): void
    {
        $step = $this->stepSessionManager->getStepData() ?? $this->activeTheme->getInitialStep();

        if (!$step?->layoutHandle) {
            return;
        }

        $this->layout->getUpdate()->removeHandle($step->layoutHandle);
        $this->addLayoutHandle($step->layoutHandle);
        $this->addHyvaLayoutHandle($step->layoutHandle);
    }

    private function addHyvaLayoutHandle(string $layoutHandle): void
    {
        if (!$this->currentTheme->isHyva()) {
            return;
        }

        $this->addLayoutHandle("hyva_{$layoutHandle}");
    }
}
