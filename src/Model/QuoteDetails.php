<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total as AddressTotal;
use MageHx\MahxCheckout\Data\PaymentMethodData;

class QuoteDetails
{
    public function __construct(
        private readonly CheckoutSession $checkoutSession,
        private readonly CartRepositoryInterface $quoteRepository,
    ) {
    }

    public function getId(): int
    {
        return (int) $this->getInstance()->getId();
    }

    public function getInstance(): Quote
    {
        return $this->quoteRepository->getActive($this->checkoutSession->getQuoteId());
    }

    public function getShippingAddress(): Quote\Address
    {
        return $this->getInstance()->getShippingAddress();
    }

    public function getStoreId(): int
    {
        return (int) $this->getInstance()->getStoreId();
    }

    public function getShippingAddressCountry(): string
    {
        return $this->getShippingAddress()->getCountry() ?: '';
    }

    public function getQuoteCustomerEmail(): string
    {
        return $this->getInstance()->getCustomerEmail() ?: '';
    }

    public function getShippingMethodDescription(): string
    {
        return $this->getShippingAddress()->getShippingDescription() ?? '';
    }

    public function getBillingAddress(): Quote\Address
    {
        return $this->getInstance()->getBillingAddress();
    }

    public function isBillingSameAsShipping(): bool
    {
       return (bool) $this->getShippingAddress()->getSameAsBilling();
    }

    public function getPaymentMethodData(): PaymentMethodData
    {
        $payment = $this->getPaymentMethod();

        return PaymentMethodData::from([
            'code' => $payment->getMethod() ? $payment->getMethodInstance()->getCode() : '',
            'title' => $payment->getMethod() ? $payment->getMethodInstance()->getTitle() : '',
        ]);
    }

    public function getPaymentMethod(): Quote\Payment
    {
        return $this->getInstance()->getPayment();
    }

    /**
     * @return AddressTotal[]
     */
    public function getTotals(): array
    {
        return $this->getInstance()->getTotals();
    }

    /**
     * @return Quote\Address[]
     */
    public function getAllAddresses(): array
    {
        return $this->getInstance()->getAllAddresses();
    }

    public function getShippingMethod(): ?string
    {
        return $this->getInstance()->getShippingAddress()->getShippingMethod();
    }

    public function isVirtualQuote(): bool
    {
        return $this->getInstance()->isVirtual();
    }
}
