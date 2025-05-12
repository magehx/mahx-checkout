<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use Magento\Customer\Model\Attribute;
use Magento\Framework\Serialize\Serializer\Json;
use MageHx\MahxCheckout\Data\AddressFieldAttributes;
use MageHx\MahxCheckout\Model\CustomerAddress;
use MageHx\MahxCheckout\Model\EventDispatcher;
use MageHx\MahxCheckout\Model\FieldRenderer\FieldRendererInterface;
use MageHx\MahxCheckout\Model\FieldRenderer\RendererPool as AddressFieldRendererPool;
use MageHx\MahxCheckout\Model\ShippingAddressForm;

class AddressFieldManager
{
    public function __construct(
        private readonly Json $jsonSerializer,
        private readonly EventDispatcher $eventDispatcher,
        private readonly CustomerAddress $customerAddress,
        private readonly AddressFieldRendererPool $addressFieldRendererPool,
    ) {
    }

    /**
     * @return AddressFieldAttributes[]
     */
    public function getAddressFieldList(string $form): array
    {
        $fields = [];

        /** @var Attribute $attribute */
        foreach ($this->customerAddress->getAddressFormAttributes() as $attribute) {
            if (! $attribute->getIsVisible()) {
                continue;
            }

            $fields[$attribute->getAttributeCode()] = new AddressFieldAttributes(
                name: $attribute->getAttributeCode(),
                label: __($attribute->getStoreLabel())->render(),
                type: $attribute->getFrontendInput(),
                required: (bool) $attribute->getIsRequired(),
                form: $form,
                rules: $attribute->getValidateRules(),
                sortOrder: (int) $attribute->getSortOrder()
            );

            if ($attribute->getFrontendInput() === 'multiline') {
                $fields[$attribute->getAttributeCode()]->additionalData['multilineCount'] =
                    (int) $attribute->getMultilineCount();
            }
        }

        $transportFields = $this->eventDispatcher->dispatchAddressFormFieldsPrepared(['fields' => $fields]);

        return $transportFields->getData('fields');
    }

    public function getRenderForAddressField(AddressFieldAttributes $fieldAttributes): FieldRendererInterface
    {
        return $this->addressFieldRendererPool->getRenderer($fieldAttributes);
    }

    public function prepareAddressFieldsDataForJs(array $fields): string
    {
        $data = [];

        foreach ($fields as $field) {
            $data[$field->name] = [
                'name' => $field->name,
                'required' => $field->required,
                'rules' => $field->rules,
                'type' => $field->type,
            ];
        }

        return $this->jsonSerializer->serialize($data);
    }
}
