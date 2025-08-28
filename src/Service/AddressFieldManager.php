<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use MageHx\MahxCheckout\Data\FormField\BaseFormFieldMeta;
use MageHx\MahxCheckout\Data\FormField\MultilineFieldMeta;
use MageHx\MahxCheckout\Model\Config;
use MageHx\MahxCheckout\Data\FormFieldConfig;
use MageHx\MahxCheckout\Model\CustomerAddress;
use MageHx\MahxCheckout\Model\EventDispatcher;
use MageHx\MahxCheckout\Model\FieldRenderer\FieldRendererInterface;
use MageHx\MahxCheckout\Model\FieldRenderer\RendererPool as AddressFieldRendererPool;

class AddressFieldManager
{
    public function __construct(
        private readonly Config $config,
        private readonly EventDispatcher $eventDispatcher,
        private readonly CustomerAddress $customerAddress,
        private readonly AddressFieldRendererPool $addressFieldRendererPool,
    ) {
    }

    /**
     * @return FormFieldConfig[]
     */
    public function getAddressFieldList(string $form): array
    {
        // Step 1: Collect base fields from Magento attributes
        $fields = $this->collectAddressAttributes($form);

        // Step 2: Apply overrides and add extra fields from config mapping
        $fields = $this->applyConfigMapping($fields, $form);

        // Step 3: Allow extensions via event dispatcher
        $transportFields = $this->eventDispatcher->dispatchAddressFormFieldsPrepared(['fields' => $fields]);

        return $transportFields->getData('fields');
    }

    public function getRendererForAddressField(FormFieldConfig $fieldAttributes): FieldRendererInterface
    {
        return $this->addressFieldRendererPool->getRenderer($fieldAttributes);
    }

    /**
     * Build base form fields from Magento's customer address attributes.
     */
    private function collectAddressAttributes(string $form): array
    {
        $fields = [];

        foreach ($this->customerAddress->getAddressFormAttributes() as $attribute) {
            if (!$attribute->getIsVisible()) {
                continue;
            }

            $config = new FormFieldConfig(
                name: $attribute->getAttributeCode(),
                label: __($attribute->getStoreLabel())->render(),
                type: $attribute->getFrontendInput(),
                required: (bool) $attribute->getIsRequired(),
                form: $form,
                sortOrder: (int) $attribute->getSortOrder(),
                meta: new BaseFormFieldMeta(),
            );

            if ($attribute->getFrontendInput() === 'multiline') {
                $config->meta = new MultilineFieldMeta((int) $attribute->getMultilineCount());
            }

            $fields[$attribute->getAttributeCode()] = $config;
        }

        return $fields;
    }

    /**
     * Apply overrides or new fields from configured render mapping.
     */
    private function applyConfigMapping(array $fields, string $form): array
    {
        foreach ($this->config->getAddressRenderMapping() as $attributeMap) {
            if (empty($attributeMap['code'])) {
                continue;
            }

            $code = $attributeMap['code'];

            // Update existing field
            if (isset($fields[$code])) {
                $field = $fields[$code];

                if (!($attributeMap['canShow'] ?? true)) {
                    unset($fields[$code]);
                    continue;
                }

                $field->label     = $attributeMap['label']     ?? $field->label;
                $field->required  = isset($attributeMap['required'])
                    ? (bool) $attributeMap['required']
                    : $field->required;
                $field->sortOrder = $attributeMap['sortOrder'] ?? $field->sortOrder;

                if (isset($attributeMap['width'])) {
                    $field->meta->width = (int) $attributeMap['width'];
                }

                continue;
            }

            // Add new field from config
            $meta = new BaseFormFieldMeta();
            if (isset($attributeMap['width'])) {
                $meta->width = (int) $attributeMap['width'];
            }

            $fields[$code] = new FormFieldConfig(
                name: $code,
                label: __($attributeMap['label'])->render(),
                type: $attributeMap['type'] ?? 'text',
                required: (bool)($attributeMap['required'] ?? false),
                form: $form,
                sortOrder: (int)($attributeMap['sortOrder'] ?? 1000),
                meta: $meta,
            );
        }

        return $fields;
    }
}
