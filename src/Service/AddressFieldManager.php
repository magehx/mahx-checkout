<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use MageHx\MahxCheckout\Data\FormField\BaseFormFieldMeta;
use MageHx\MahxCheckout\Data\FormField\MultilineFieldMeta;
use MageHx\MahxCheckout\Enum\AdditionalFieldAttribute;
use Magento\Customer\Model\Attribute;
use Magento\Framework\Serialize\Serializer\Json;
use MageHx\MahxCheckout\Data\FormFieldConfig;
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
     * @return FormFieldConfig[]
     */
    public function getAddressFieldList(string $form): array
    {
        $fields = [];

        /** @var Attribute $attribute */
        foreach ($this->customerAddress->getAddressFormAttributes() as $attribute) {
            if (! $attribute->getIsVisible()) {
                continue;
            }

            $fields[$attribute->getAttributeCode()] = new FormFieldConfig(
                name: $attribute->getAttributeCode(),
                label: __($attribute->getStoreLabel())->render(),
                type: $attribute->getFrontendInput(),
                required: (bool) $attribute->getIsRequired(),
                form: $form,
                sortOrder: (int) $attribute->getSortOrder(),
                meta: new BaseFormFieldMeta(),
            );

            if ($attribute->getFrontendInput() === 'multiline') {
                $lineCount = (int) $attribute->getMultilineCount();
                $fields[$attribute->getAttributeCode()]->meta = new MultilineFieldMeta($lineCount);
            }
        }

        $transportFields = $this->eventDispatcher->dispatchAddressFormFieldsPrepared(['fields' => $fields]);

        return $transportFields->getData('fields');
    }

    public function getRenderForAddressField(FormFieldConfig $fieldAttributes): FieldRendererInterface
    {
        return $this->addressFieldRendererPool->getRenderer($fieldAttributes);
    }
}
