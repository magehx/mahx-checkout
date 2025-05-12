<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Controller\Step;

use Exception;
use Magento\Framework\Controller\ResultInterface;
use MageHx\MahxCheckout\Controller\Form\ComponentAction;

class GetStepContent extends ComponentAction
{
    public function execute(): ResultInterface
    {
        $requestedStep = (string) $this->getRequest()->getParam('step');
        try {
            $stepToLoad = $this->stepValidationService->getValidStepFor($requestedStep);
            $this->stepSessionManager->setStepData($stepToLoad);
            return $this->withCurrentStepPushUrlHeader($this->getCheckoutContentResponse());
        } catch (Exception) {
            return $this->withNoReswapHeader($this->getEmptyResponse());
        }
    }
}
