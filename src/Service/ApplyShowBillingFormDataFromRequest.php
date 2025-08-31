<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use MageHx\MahxCheckout\Model\CheckoutDataStorage;
use Magento\Framework\App\RequestInterface;

class ApplyShowBillingFormDataFromRequest
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly CheckoutDataStorage $checkoutDataStorage,
    ) {
    }

    public function apply(): void
    {
        $isBillingSame = (bool) $this->request->getParam('is_billing_same');
        $showForm = (bool) $this->request->getParam('show_form');
        $showCards = (bool) ($this->request->getParam('show_cards'));

        $this->checkoutDataStorage->setData([
            'is_billing_same' => $isBillingSame,
            'show_cards' => $showCards,
            'show_form' => $showForm
        ]);
    }
}
