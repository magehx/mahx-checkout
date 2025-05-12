<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use Magento\Framework\UrlInterface;
use MageHx\MahxCheckout\Data\AddressFieldAttributes;
use MageHx\MahxCheckout\Enum\AdditionalFieldAttribute as AFAttributes;
use MageHx\MahxCheckout\Model\CountryProvider;
use MageHx\MahxCheckout\Model\CustomerAddress;

class PrepareRegionFieldAttribute
{
    public function __construct(
        private readonly UrlInterface $urlBuilder,
        private readonly CustomerAddress $customerAddress,
        private readonly CountryProvider $countryProvider,
        private readonly GenerateBlockHtml $generateBlockHtmlService,
    ) {
    }

    public function execute(
        string $country,
        ?string $form = null,
        ?AddressFieldAttributes $regionField = null
    ): ?AddressFieldAttributes {
       if (!$regionField) {
           $region = $this->customerAddress->getAddressFormAttribute('region');
           $regionField = new AddressFieldAttributes(
               name: $region->getAttributeCode(),
               label: __($region->getStoreLabel())->render(),
               type: $region->getFrontendInput(),
               required: (bool) $region->getIsRequired(),
               form: $form,
               rules: $region->getValidateRules(),
               sortOrder: (int) $region->getSortOrder()
           );
       }

        $regionOptions = $this->countryProvider->getRegionOptionsByCountry($country);

        if (empty($regionOptions)) {
            return $regionField;
        }

        $regionField->type = 'select';
        $regionField->required = true;
        $regionField->additionalData[AFAttributes::OPTIONS->value] = $regionOptions;
        $regionField->additionalData[AFAttributes::DEFAULT_OPTION_LABEL->value] = __('Please select your region');

        return $regionField;
    }

    public function addAdditionalAttributesToRegion(
        AddressFieldAttributes $regionField,
        string $formId
    ): AddressFieldAttributes {
        $regionField->additionalData[AFAttributes::INPUT_EXTRA_ATTRIBUTES->value] = [
            'hx-get'       => $this->urlBuilder->getUrl('mahxcheckout/form/getRegionInput', ['form' => $formId]),
            'hx-swap'      => 'outerHTML',
            'hx-target'    => 'closest .form-control',
            'hx-trigger'   => "mahxcheckout-{$formId}-country_id-changed from:window",
            'hx-include'   => "#{$formId}-country_id",
            'hx-vals' => '{"form":"' . $formId . '"}',
            'hx-indicator' => "#{$formId}-region-loader",
        ];

        $regionField->additionalData[AFAttributes::WRAPPER_ELEM_EXTRA_CLASS->value] = 'relative';
        $regionField->additionalData[AFAttributes::AFTER_INPUT_HTML->value]
            = $this->generateBlockHtmlService->getLoaderHtml("{$formId}-region-loader");

        return $regionField;
    }
}
