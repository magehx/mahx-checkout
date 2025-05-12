<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Block;

use Magento\Framework\View\Element\Template;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use MageHx\MahxCheckout\Service\EstimateShippingMethodsService;

class ShippingMethodsForm extends Template
{
    public function __construct(
        private readonly EstimateShippingMethodsService $estimateShippingMethodsService,
        Template\Context $context,
        array $data = [],
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return ShippingMethodInterface[]
     */
    public function getShippingMethods(): array
    {
        return $this->getEstimatedMethods() ?? $this->estimateShippingMethodsService->estimateByShippingAddress();
    }

    /**
     * @return ?ShippingMethodInterface[]
     */
    public function getEstimatedMethods(): ?array
    {
        return $this->getData('estimated_methods');
    }
}
