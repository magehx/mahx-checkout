<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Controller\Form;

use Exception;
use MageHx\HtmxActions\Enums\HtmxCoreAttributes;
use Magento\Framework\Controller\ResultInterface;
use MageHx\MahxCheckout\Controller\Form\ComponentAction\Context;
use MageHx\MahxCheckout\Data\ShippingEstimateFieldsData;
use MageHx\MahxCheckout\Enum\CheckoutForm;
use MageHx\MahxCheckout\Service\AddressFieldManager;
use MageHx\MahxCheckout\Service\EstimateShippingMethodsService;
use MageHx\MahxCheckout\Service\PrepareRegionFieldAttribute;

class EstimateShippingMethods extends ComponentAction
{
    public function __construct(
        Context $context,
        private readonly AddressFieldManager $addressFieldManager,
        private readonly EstimateShippingMethodsService $estimateShippingMethodsService,
        private readonly PrepareRegionFieldAttribute $prepareRegionFieldAttributeService,
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        try {
            $addressData = $this->getAddressData();
            $addressData->validate();

            $this->setHandles($this->getCurrentStepLayoutHandles());
            $this->estimateAndSetShippingMethods($addressData);

            return $this->isCountryTriggered()
                ? $this->respondWithRegionAndShipping($addressData)
                : $this->getComponentResponse('shipping.methods.form');
        } catch (Exception) {
            return $this->withNoReswapHeader($this->getEmptyResponse());
        }
    }

    private function getAddressData(): ShippingEstimateFieldsData
    {
        return ShippingEstimateFieldsData::from([
            'postcode' => (string) $this->getPostData('postcode', ''),
            'country'  => (string) $this->getPostData('country_id', ''),
            'region'   => (string) $this->getPostData('region', ''),
        ]);
    }

    private function isCountryTriggered(): bool
    {
        return $this->htmxHeaderManager->getReader()->isTriggerSameAs('country_id');
    }

    private function estimateAndSetShippingMethods(ShippingEstimateFieldsData $addressData): void
    {
        $methods = $this->estimateShippingMethodsService->estimateByFields($addressData);
        $this->getBlock('shipping.methods.form', $this->handles, ['estimated_methods' => $methods]);
    }

    private function respondWithRegionAndShipping(ShippingEstimateFieldsData $addressData): ResultInterface
    {
        $this->checkoutDataStorage->setData(['country_id' => $addressData->country]);
        $regionFieldHtml = $this->renderUpdatedRegionField($addressData->country);

        return $this->getComponentResponse('shipping.methods.form', additionalHtml: $regionFieldHtml);
    }

    private function renderUpdatedRegionField(string $country): string
    {
        $regionField = $this->prepareRegionFieldAttributeService->execute(
            country: $country,
            form: CheckoutForm::SHIPPING_ADDRESS->value
        );
        $regionField->meta->wrapperElemExtraAttributes[HtmxCoreAttributes::swapOOB->value] = 'true';

        return $this->addressFieldManager->getRendererForAddressField($regionField)->render($regionField);
    }
}
