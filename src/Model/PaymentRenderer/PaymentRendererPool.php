<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\PaymentRenderer;

use MageHx\MahxCheckout\Data\PaymentMethodData;
use Magento\Quote\Api\Data\PaymentMethodInterface;

class PaymentRendererPool
{
    public function __construct(
        private readonly array $rendererDataList = []
    ) {
    }

    public function getPaymentDataFor(PaymentMethodInterface $paymentMethod): PaymentMethodData
    {
        $methodCode = $paymentMethod->getCode();
        $dataClass = $this->rendererDataList[$methodCode]['dataClass'] ?? PaymentMethodData::class;

        return $dataClass::from(['code' => $methodCode, 'title' => $paymentMethod->getTitle()]);
    }

    public function getRendererTemplateFor(PaymentMethodData $paymentMethodData): string
    {
        $defaultTemplate = $this->rendererDataList['default']['template'];

        return $this->rendererDataList[$paymentMethodData->code]['template'] ?? $defaultTemplate;
    }
}
