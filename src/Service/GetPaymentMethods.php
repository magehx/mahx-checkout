<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use Exception;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use MageHx\MahxCheckout\Data\PaymentMethodData;

class GetPaymentMethods
{
    public function __construct(
        private readonly PaymentMethodManagementInterface $paymentMethodManagement,
    ) {
    }

    /**
     * @return PaymentMethodData[]
     */
    public function execute(int $cartId): array
    {
        try {
            $methods = [];
            foreach($this->paymentMethodManagement->getList($cartId) as $paymentMethod) {
                $methods[] = PaymentMethodData::from([
                    'code' => $paymentMethod->getCode(),
                    'title' => $paymentMethod->getTitle(),
                ]);
            }
            return $methods;
        } catch (Exception) {
            return [];
        }
    }
}
