<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Controller\Payment;

use Magento\Framework\Controller\ResultInterface;
use MageHx\MahxCheckout\Controller\Form\ComponentAction;

class BillingInformation extends ComponentAction
{

    public function execute(): ResultInterface
    {
        return $this->getComponentResponse('billing.address.form');
    }
}
