<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Controller\Shipping;

use Exception;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;
use MageHx\MahxCheckout\Controller\Form\ComponentAction;
use MageHx\MahxCheckout\Controller\Form\ComponentAction\Context;
use MageHx\MahxCheckout\Model\QuoteDetails;
use MageHx\MahxCheckout\Service\NewShippingAddressManager;
use MageHx\MahxCheckout\Service\PrepareShippingInformationFromRequest;
use MageHx\MahxCheckout\Service\SaveShippingInformation as SaveShippingInformationService;

class SaveShippingInformation extends ComponentAction
{
    public function __construct(
        Context $context,
        private readonly QuoteDetails $quote,
        private readonly LoggerInterface $logger,
        private readonly NewShippingAddressManager $newShippingAddressManager,
        private readonly SaveShippingInformationService $saveShippingInfoService,
        private readonly PrepareShippingInformationFromRequest $prepareShippingInfoService,
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $shippingInfo = $this->prepareShippingInfoService->execute((array)$this->getRequest()->getPost());

        try {
            $shippingInfo->validate();
            $this->newShippingAddressManager->keepShippingAddressAsNew($shippingInfo);
            $this->saveShippingInfoService->execute($this->quote->getId(), $shippingInfo);

            if (!$this->isStepSaveDataRequest()) {
                return $this->getCheckoutContentResponse();
            }

            $this->proceedToNextStep();
            return $this->withCurrentStepPushUrlHeader($this->getCheckoutContentResponse());
        } catch (Exception $e) {
            $this->logger->error('MAHXCheckout::SaveShippingInformation::failed', ['exception' => $e]);
            $this->prepareErrorNotificationsWithFormData($shippingInfo->toArray(), $e);
            return $this->withNoReswapHeader($this->getNotificationsResponse());
        }
    }
}
