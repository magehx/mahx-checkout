<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Controller\Shipping;

use Magento\Framework\Controller\ResultInterface;
use MageHx\MahxCheckout\Controller\Form\ComponentAction;

class GetNewAddressForm extends ComponentAction
{
    public function execute(): ResultInterface
    {
        return $this->getComponentResponse('shipping.address.cards.form');
    }
}
