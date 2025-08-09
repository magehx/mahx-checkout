<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Controller\Form;

use Exception;
use Magento\Framework\Controller\ResultInterface;
use MageHx\MahxCheckout\Controller\Form\ComponentAction\Context;
use MageHx\MahxCheckout\Data\GetRegionInputData;
use MageHx\MahxCheckout\Service\AddressFieldManager;
use MageHx\MahxCheckout\Service\PrepareRegionFieldAttribute;

class GetRegionInput extends ComponentAction
{
    public function __construct(
        Context $context,
        private readonly AddressFieldManager $addressFieldManager,
        private readonly PrepareRegionFieldAttribute $prepareRegionFieldAttributeService
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        try {
            $input = $this->getValidatedInput();

            $this->checkoutDataStorage->setData(['country_id' => $input->country]);

            $html = $this->renderRegionField($input->country, $input->form);

            return $this->getEmptyResponse()->setContents($html);
        } catch (Exception) {
            return $this->getEmptyResponse()->setHeader('HX-Swap', 'none');
        }
    }

    private function getValidatedInput(): GetRegionInputData
    {
        $input = GetRegionInputData::from([
            'form' => (string) $this->getRequest()->getParam('form', ''),
            'country' => (string) $this->getRequest()->getParam('country_id', ''),
        ]);

        $input->validate();

        return $input;
    }

    private function renderRegionField(string $country, string $form): string
    {
        $regionField = $this->prepareRegionFieldAttributeService->execute($country, $form);
        $renderer = $this->addressFieldManager->getRenderForAddressField($regionField);

        return $renderer->render($regionField);
    }
}
