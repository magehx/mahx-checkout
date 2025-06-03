<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use MageHx\MahxCheckout\Model\EventDispatcher;
use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use MageHx\MahxCheckout\Data\PaymentInformation;
use MageHx\MahxCheckout\Model\QuoteDetails;

class PlaceOrderService
{
    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly EventDispatcher $eventDispatcher,
        private readonly CustomerSession $customerSession,
        private readonly QuoteIdMaskFactory $quoteIdMaskFactory,
        private readonly PaymentInformationManagementInterface $paymentInformationManagement,
        private readonly GuestPaymentInformationManagementInterface $guestPaymentInformationManagement,
    ) {}

    public function execute(PaymentInformation $paymentInformation): int
    {
        $payment = $this->preparePayment($paymentInformation);
        $transport = $this->eventDispatcher->dispatchPlaceOrderSavePaymentBefore([
            'payment' => $payment,
            'payment_information' => $paymentInformation,
        ]);
        $payment = $transport->getData('payment');

        if ($this->customerSession->isLoggedIn()) {
            return $this->paymentInformationManagement->savePaymentInformationAndPlaceOrder(
                $this->quote->getId(),
                $payment
            );
        }

        return $this->guestPaymentInformationManagement->savePaymentInformationAndPlaceOrder(
            $this->getMaskedCartId(),
            $this->quote->getQuoteCustomerEmail(),
            $payment
        );
    }

    public function preparePayment(PaymentInformation $paymentInformation): PaymentInterface
    {
        $paymentData = $paymentInformation->paymentMethod;
        $payment = $this->quote->getPaymentMethod();
        $payment->setMethod($paymentData->method);

        return $payment;
    }

    private function getMaskedCartId(): string
    {
        return $this->quoteIdMaskFactory->create()->load($this->quote->getId(), 'quote_id')->getMaskedId();
    }
}
