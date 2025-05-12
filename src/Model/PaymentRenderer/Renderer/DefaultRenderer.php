<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\PaymentRenderer\Renderer;

use Magento\Framework\View\LayoutInterface;
use MageHx\MahxCheckout\Block\PaymentRenderer;
use MageHx\MahxCheckout\Data\PaymentMethodData;
use MageHx\MahxCheckout\Model\PaymentRenderer\PaymentRendererInterface;

class DefaultRenderer implements PaymentRendererInterface
{
    public function __construct(private readonly LayoutInterface $layout)
    {
    }

    public function render(PaymentMethodData $paymentMethodData): string
    {
        /** @var PaymentRenderer $block */
        $block = $this->layout->createBlock(PaymentRenderer::class)
            ->setTemplate('MageHx_MahxCheckout::payment/renderer/default.phtml');
        $block->setPaymentData($paymentMethodData);

        return $block->toHtml();
    }
}
