<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\PaymentRenderer;

use MageHx\MahxCheckout\Data\PaymentMethodData;

interface PaymentRendererInterface
{
    public function render(PaymentMethodData $paymentMethodData): string;
}
