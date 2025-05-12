<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Controller\Form;

use Exception;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use MageHx\MahxCheckout\Controller\Form\ComponentAction\Context;
use MageHx\MahxCheckout\Service\EstimateShippingMethodsService;

class EstimateShippingMethodsByAddressId extends ComponentAction
{
    public function __construct(
        Context $context,
        private readonly EstimateShippingMethodsService $estimateShippingMethodsService,
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        try {
            $addressId = (int) $this->getRequest()->getParam('address_id');

            if ($addressId) {
                return $this->estimateShippingAndSendResponse($addressId);
            }

            throw new Exception('No address id provided');

        } catch (Exception) {
            return $this->withNoReswapHeader($this->getEmptyResponse());
        }

    }

    private function estimateShippingAndSendResponse(int $addressId): ResultInterface
    {
        $this->setHandles($this->getCurrentStepLayoutHandles());
        $estimatedMethods = $this->estimateShippingMethodsService->estimateByAddressId($addressId);

        $this->getBlock(
            'shipping.methods.form',
            $this->handles,
            ['estimated_methods' => $estimatedMethods]
        );

        return $this->getComponentResponse('shipping.methods.form');
    }
}
