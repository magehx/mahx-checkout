<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

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
        private readonly CustomerSession $customerSession,
        private readonly QuoteIdMaskFactory $quoteIdMaskFactory,
        private readonly PaymentInformationManagementInterface $paymentInformationManagement,
        private readonly GuestPaymentInformationManagementInterface $guestPaymentInformationManagement,
    ) {}

    public function execute(PaymentInformation $paymentInformation): int
    {
        $payment = $this->preparePayment($paymentInformation);

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

    private function preparePayment(PaymentInformation $paymentInformation): PaymentInterface
    {
        $payment = $this->quote->getPaymentMethod();
        $payment->setMethod($paymentInformation->paymentMethod->method);

        foreach ($paymentInformation->paymentMethod->additionalData ?? [] as $key => $value) {
            $payment->setAdditionalInformation($key, $value);
        }

        return $payment;
    }

    private function getMaskedCartId(): string
    {
        return $this->quoteIdMaskFactory->create()->load($this->quote->getId(), 'quote_id')->getMaskedId();
    }
}
