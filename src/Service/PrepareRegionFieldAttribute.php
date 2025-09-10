<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use MageHx\HtmxActions\Data\HxAttributesData;
use MageHx\HtmxActions\Data\MageUrlData;
use MageHx\HtmxActions\Enums\HtmxSwapOption;
use MageHx\MahxCheckout\Data\FormField\BaseFormFieldMeta;
use MageHx\MahxCheckout\Data\FormField\SelectFieldMeta;
use MageHx\MahxCheckout\Data\FormFieldConfig;
use MageHx\MahxCheckout\Model\Config;
use MageHx\MahxCheckout\Model\CountryProvider;
use MageHx\MahxCheckout\Model\CustomerAddress;

class PrepareRegionFieldAttribute
{
    public function __construct(
        private readonly Config $config,
        private readonly CustomerAddress $customerAddress,
        private readonly CountryProvider $countryProvider,
        private readonly GenerateBlockHtml $generateBlockHtmlService,
    ) {
    }

    public function execute(
        string $country,
        ?string $form = null,
        ?FormFieldConfig $regionField = null
    ): ?FormFieldConfig {
       if (!$regionField) {
           $region = $this->customerAddress->getAddressFormAttribute('region');
           $regionField = new FormFieldConfig(
               name: $region->getAttributeCode(),
               label: __($region->getStoreLabel())->render(),
               type: $region->getFrontendInput(),
               required: (bool) $region->getIsRequired(),
               form: $form,
               sortOrder: (int) $region->getSortOrder(),
               meta: new BaseFormFieldMeta(),
           );
       }
        $regionField->required = in_array($country, $this->config->getRegionRequiredCountries());
        $regionOptions = $this->countryProvider->getRegionOptionsByCountry($country);

        if (empty($regionOptions)) {
            return $regionField;
        }

        $regionField->type = 'select';

        if (!$regionField->meta instanceof SelectFieldMeta) {
            $regionFieldMeta = SelectFieldMeta::from([
                'options' => $regionOptions,
                'defaultOptionLabel' => __('Please select your region'),
            ]);
            $regionFieldMeta->copyFrom($regionField->meta);
            $regionField->meta = $regionFieldMeta;
        }

        return $regionField;
    }

    public function addAdditionalAttributesToRegion(
        FormFieldConfig $regionField,
        string $formId
    ): FormFieldConfig {
        $regionField->meta->inputElementHxAttributes = HxAttributesData::from([
            'post' => MageUrlData::from([
                'path' => 'mahxcheckout/form/getRegionInput',
                'params' => ['form' => $formId]
            ]),
            'swap'      => HtmxSwapOption::outerHTML,
            'target'    => 'closest .form-control',
            'trigger'   => "mahxcheckout-{$formId}-country_id-changed from:body",
            'include'   => ["#{$formId}-country_id", '[name=form_key]'],
            'vals'      =>  ['form' => $formId],
            'indicator' => "#{$formId}-region-loader",
        ]);

        $regionField->meta->wrapperElemExtraClasses = 'relative';
        $regionField->meta->afterInputHtml = $this->generateBlockHtmlService->getLoaderHtml(
            "{$formId}-region-loader"
        );

        return $regionField;
    }
}
