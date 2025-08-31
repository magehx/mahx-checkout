<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Controller\Billing;

use MageHx\MahxCheckout\Controller\Form\ComponentAction\Context;
use MageHx\MahxCheckout\Service\ApplyShowBillingFormDataFromRequest;
use Magento\Framework\Controller\ResultInterface;
use MageHx\MahxCheckout\Controller\Form\ComponentAction;

class EditBilling extends ComponentAction
{
    public function __construct(
        Context $context,
        private readonly ApplyShowBillingFormDataFromRequest $applyShowBillingFormData,
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $this->applyShowBillingFormData->apply();

        return $this->getComponentResponse('billing.address.section');
    }
}
