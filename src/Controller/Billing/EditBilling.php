<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Controller\Billing;

use Magento\Framework\Controller\ResultInterface;
use MageHx\MahxCheckout\Controller\Form\ComponentAction;

class EditBilling extends ComponentAction
{
    public function execute(): ResultInterface
    {
        $isBillingSame = (bool) $this->getRequest()->getParam('is_billing_same');
        $showForm = (bool) $this->getRequest()->getParam('show_form');
        $isEdit = (bool) ($this->getRequest()->getParam('is_edit', true));

        if ($isEdit) {
            $this->formDataStorage->setData([
                'is_billing_same' => $isBillingSame,
                'is_edit' => true,
                'show_form' => $showForm
            ]);
        }

        return $this->getComponentResponse('billing.address.section');
    }
}
