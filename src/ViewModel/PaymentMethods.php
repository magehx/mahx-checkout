<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

use MageHx\MahxCheckout\Block\PaymentRenderer;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use MageHx\MahxCheckout\Data\PaymentMethodData;
use MageHx\MahxCheckout\Model\PaymentRenderer\PaymentRendererPool;
use MageHx\MahxCheckout\Model\QuoteDetails;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;

class PaymentMethods implements ArgumentInterface
{
    private ?array $methods = null;

    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly Json $jsonSerializer,
        private readonly LayoutInterface $layout,
        private readonly PaymentRendererPool $paymentRendererPool,
        private readonly PaymentMethodManagementInterface $paymentMethodManagement,
    ) {
    }

    /**
     * @return PaymentMethodData[]
     */
    public function getPaymentMethods(): array
    {
        if (!$this->methods) {
            foreach($this->paymentMethodManagement->getList($this->quote->getId()) as $paymentMethod) {
                $paymentData = $this->paymentRendererPool->getPaymentDataFor($paymentMethod);
                $this->methods[$paymentMethod->getCode()] = $paymentData;
            }
        }

        return $this->methods;
    }

    public function getSelectedMethod(): PaymentMethodData
    {
        $selectedMethod = $this->quote->getPaymentMethod()->getMethod();

        return $selectedMethod ? $this->getPaymentMethods()[$selectedMethod] : PaymentMethodData::from(['code' => '']);
    }

    public function renderPayment(PaymentMethodData $paymentMethodData): string
    {
        $template = $this->paymentRendererPool->getRendererTemplateFor($paymentMethodData);

        return $this->layout->createBlock(PaymentRenderer::class)
            ->setTemplate($template)
            ->setPaymentData($paymentMethodData)
            ->toHtml();
    }

    public function isVirtualCart(): bool
    {
        return $this->quote->isVirtualQuote();
    }

    public function getValidationDataJson(): string
    {
        return $this->jsonSerializer->serialize($this->getSelectedMethod()->rules());
    }
}
