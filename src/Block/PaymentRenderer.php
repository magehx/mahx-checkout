<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Block;

use Magento\Framework\View\Element\Template;
use MageHx\MahxCheckout\Data\PaymentMethodData;
use MageHx\MahxCheckout\Model\QuoteDetails;

class PaymentRenderer extends Template implements PaymentRendererBlockInterface
{
    private ?PaymentMethodData $paymentData = null;

    public function __construct(
        private readonly QuoteDetails $quote,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function setPaymentData(PaymentMethodData $paymentData): self
    {
        $this->paymentData = $paymentData;
        return $this;
    }

    public function getPaymentData(): ?PaymentMethodData
    {
        return $this->paymentData;
    }

    public function isSelected(): bool
    {
        return $this->paymentData->code === $this->quote->getPaymentMethod()->getMethod();
    }
}
