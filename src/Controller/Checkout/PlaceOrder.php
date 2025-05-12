<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Controller\Checkout;

use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;
use MageHx\MahxCheckout\Controller\Form\ComponentAction;
use MageHx\MahxCheckout\Controller\Form\ComponentAction\Context;
use MageHx\MahxCheckout\Data\PaymentInformation;
use MageHx\MahxCheckout\Service\PlaceOrderService;

class PlaceOrder extends ComponentAction
{
    public function __construct(
        Context $context,
        private readonly LoggerInterface $logger,
        private readonly PlaceOrderService $placeOrderService,
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $paymentInformation = $this->preparePaymentInformation();
        try {
            $paymentInformation->validate();
            $this->placeOrderService->execute($paymentInformation);

            return $this->getEmptyResponse()->setHeader(
                'HX-Location',
                $this->_url->getUrl('checkout/onepage/success'),
            );

        } catch (\Exception $e) {
            $this->logger->error('MahxCheckout::place_order_failed', ['exception' => $e]);
            $this->addGenericErrorMessage(__('You cannot place an order.'));
            return $this->getNotificationsResponse();
        }
    }

    private function preparePaymentInformation(): PaymentInformation
    {
        $paymentMethod = PaymentInformation\PaymentMethod::from([
            'method' => $this->getPostData('payment_method'),
        ]);

        return PaymentInformation::from(['paymentMethod' => $paymentMethod]);
    }
}
