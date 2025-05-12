<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Quote\Api\CartRepositoryInterface;
use MageHx\MahxCheckout\Data\PaymentMethodData;
use MageHx\MahxCheckout\Model\QuoteDetails;

class PaymentMethodManagement
{
    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly CustomerSession $customerSession,
        private readonly CartRepositoryInterface $quoteRepository,
        private readonly PaymentInformationManagementInterface $paymentInformationManagement,
    ) {
    }

    public function savePaymentInformation(PaymentMethodData $paymentMethodData): void
    {
        if (! $this->customerSession->isLoggedIn()) {
            $quote = $this->quoteRepository->getActive($this->quote->getId());
            $quote->getBillingAddress()->setEmail($this->quote->getQuoteCustomerEmail());
        }

        $payment = $this->quote->getPaymentMethod();
        $payment->setMethod($paymentMethodData->code);

        $this->paymentInformationManagement->savePaymentInformation($this->quote->getId(), $payment);
    }
}
