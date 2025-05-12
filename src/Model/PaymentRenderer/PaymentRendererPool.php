<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\PaymentRenderer;

use Magento\Framework\ObjectManagerInterface;

class PaymentRendererPool
{
    private array $instances = [];

    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        private readonly array $rendererClasses = []
    ) {
    }

    public function getRenderer(string $methodCode): PaymentRendererInterface
    {
        $rendererClass = $this->rendererClasses[$methodCode] ?? $this->rendererClasses['default'];

        if (!isset($this->instances[$rendererClass])) {
            $this->instances[$rendererClass] = $this->objectManager->create($rendererClass);
        }

        return $this->instances[$rendererClass];
    }
}
