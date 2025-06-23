<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model;

use Magento\Customer\Model\AttributeMetadataDataProvider as CustomerAttributeMetaDataProvider;
use Magento\Customer\Model\ResourceModel\Form\Attribute\Collection as FormAttributeCollection;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

class CustomerAddress
{
    private ?FormAttributeCollection $formAttributes = null;

    public function __construct(private readonly CustomerAttributeMetaDataProvider $customerAttributeMetaDataProvider)
    {
    }

    public function getAddressFormAttributes(): FormAttributeCollection
    {
        if (!$this->formAttributes) {
            $this->formAttributes = $this->customerAttributeMetaDataProvider->loadAttributesCollection(
                'customer_address',
                'customer_register_address'
            );
        }

        return $this->formAttributes;
    }

    public function getAddressFormAttribute(string $attributeCode): AbstractAttribute|bool
    {
        return $this->customerAttributeMetaDataProvider->getAttribute('customer_address', $attributeCode);
    }
}
