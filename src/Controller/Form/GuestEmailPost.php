<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Controller\Form;

use Exception;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use MageHx\MahxCheckout\Controller\Form\ComponentAction\Context;
use MageHx\MahxCheckout\Data\GuestEmailData;
use MageHx\MahxCheckout\Service\SaveGuestEmail;

class GuestEmailPost extends ComponentAction
{
    public function __construct(
        Context $context,
        private readonly SaveGuestEmail $saveGuestEmailService,
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $guestEmailData = GuestEmailData::from(['email' => $this->getRequest()->getParam('email')]);

        try {
            $guestEmailData->validate();
            $this->saveGuestEmailService->execute($guestEmailData);
            return $this->getComponentResponse('guest.email.form');
        } catch (Exception $e) {
            $this->prepareErrorNotificationsWithFormData($guestEmailData->toArray(), $e);
            return $this->getComponentResponse('guest.email.form', withNotification: true);
        }
    }
}
