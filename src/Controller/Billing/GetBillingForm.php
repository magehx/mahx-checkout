<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Controller\Billing;

use Magento\Framework\Controller\ResultInterface;
use MageHx\MahxCheckout\Controller\Form\ComponentAction;
use MageHx\MahxCheckout\Controller\Form\ComponentAction\Context;
use MageHx\MahxCheckout\Model\QuoteDetails;

class GetBillingForm extends ComponentAction
{
    public function __construct(
        Context $context,
        private readonly QuoteDetails $quote,
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $isBillingSame = (bool) $this->getRequest()->getParam('is_billing_same', false);

        if ($this->quote->isBillingSameAsShipping() !== $isBillingSame) {
            $this->formDataStorage->setData(['is_billing_same' => $isBillingSame]);
        }

        return $this->getComponentResponse('billing.address.section');
    }
}
