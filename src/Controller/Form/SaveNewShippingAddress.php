<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Controller\Form;

use Exception;
use Magento\Framework\Controller\ResultInterface;
use MageHx\MahxCheckout\Controller\Form\ComponentAction\Context;
use MageHx\MahxCheckout\Data\AddressData;
use MageHx\MahxCheckout\Model\QuoteDetails;
use MageHx\MahxCheckout\Service\EstimateShippingMethodsService;
use MageHx\MahxCheckout\Service\NewShippingAddressManager;

class SaveNewShippingAddress extends ComponentAction
{
    public function __construct(
        Context $context,
        private readonly QuoteDetails $quote,
        private readonly NewShippingAddressManager $newShippingAddressManager,
        private readonly EstimateShippingMethodsService $estimateShippingMethodsService,
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $addressData = AddressData::from([
            'firstname' => $this->getPostData('firstname'),
            'lastname' => $this->getPostData('lastname'),
            'street' => $this->getPostData('street'),
            'city' => $this->getPostData('city'),
            'country_id' => $this->getPostData('country_id'),
            'postcode' => $this->getPostData('postcode'),
            'region' => $this->getPostData('region'),
            'telephone' => $this->getPostData('telephone'),
        ]);

        try {
            $addressData->validate();
            $this->newShippingAddressManager->save($addressData);
            $this->prepareShippingMethodsBlock($addressData);
            return $this->getMultiComponentResponse(['shipping.address.cards', 'shipping.methods.form']);
        } catch (Exception) {
            $this->addGenericErrorMessage(__('Shipping address save encountered some problem. Please try again.'));
            return $this->getNotificationsResponse()->setHeader('HX-Reswap', 'none');
        }
    }

    private function prepareShippingMethodsBlock(AddressData $addressData): void
    {
        $estimatedMethods = $this->estimateShippingMethodsService->estimateByAddress($this->quote->getShippingAddress());
        $shippingFormBlock = $this->getBlock('shipping.methods.form', $this->getCurrentStepLayoutHandles());
        $shippingFormBlock?->setData('estimated_methods', $estimatedMethods);
        $shippingFormBlock?->setData('is_htmx_oob', true);
    }
}
