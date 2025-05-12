<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Enum;

enum CheckoutForm: string
{
    case BILLING_ADDRESS = 'billing-address-form';
    case SHIPPING_ADDRESS = 'shipping-address-form';
    case NEW_ADDRESS = 'new-address-form';
}
