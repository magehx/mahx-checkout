<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Controller\Form;

use Exception;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use MageHx\MahxCheckout\Controller\Form\ComponentAction\Context;
use MageHx\MahxCheckout\Data\PaymentMethodData;
use MageHx\MahxCheckout\Service\PaymentMethodManagement;

class PaymentMethodPost extends ComponentAction
{
    public function __construct(
        Context $context,
        private readonly PaymentMethodManagement $paymentMethodManagement,
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $paymentData = PaymentMethodData::from(['code' => $this->getRequest()->getParam('payment_method')]);

        try {
            $paymentData->validate();
            $this->paymentMethodManagement->savePaymentInformation($paymentData);
            return $this->getCheckoutContentResponse();
        } catch (Exception $e) {
            $this->prepareErrorNotificationsWithFormData($paymentData->toArray(), $e);
            return $this->withNoReswapHeader($this->getNotificationsResponse());
        }


    }
}
