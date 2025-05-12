<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use MageHx\MahxCheckout\Data\PaymentMethodData;
use MageHx\MahxCheckout\Model\PaymentRenderer\PaymentRendererPool;
use MageHx\MahxCheckout\Model\QuoteDetails;
use MageHx\MahxCheckout\Service\GetPaymentMethods;

class PaymentMethods implements ArgumentInterface
{
    private ?array $methods = null;

    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly PaymentRendererPool $paymentRendererPool,
        private readonly GetPaymentMethods $getPaymentMethodsService,
    ) {
    }

    /**
     * @return PaymentMethodData[]
     */
    public function getPaymentMethods(): array
    {
        if (!$this->methods) {
            $this->methods = $this->getPaymentMethodsService->execute($this->quote->getId());
        }

        return $this->methods;
    }

    public function getSelectedMethod(): PaymentMethodData
    {
        return $this->quote->getPaymentMethodData();
    }

    public function renderPayment(PaymentMethodData $paymentMethodData): string
    {
        $renderer = $this->paymentRendererPool->getRenderer($paymentMethodData->code);

        return $renderer->render($paymentMethodData);
    }
}
