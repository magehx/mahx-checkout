<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Block;

use MageHx\MahxCheckout\Data\PaymentMethodData;

interface PaymentRendererBlockInterface
{
    public function setPaymentData(PaymentMethodData $paymentData): self;
    public function getPaymentData(): ?PaymentMethodData;
    public function isSelected(): bool;
}
