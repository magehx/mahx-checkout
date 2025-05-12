<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Block\Address;

use Magento\Framework\View\Element\Template;
use MageHx\MahxCheckout\Data\AddressFieldAttributes;
use MageHx\MahxCheckout\Enum\AdditionalFieldAttribute;

class FieldRenderer extends Template
{
    private ?AddressFieldAttributes $fieldAttributes = null;

    protected $_template = 'MageHx_MahxCheckout::ui/address/fields/text.phtml';

    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function setFieldAttributes(AddressFieldAttributes $attributes): self
    {
        $this->fieldAttributes = $attributes;

        return $this;
    }

    public function getFieldAttributes(): ?AddressFieldAttributes
    {
        return $this->fieldAttributes;
    }

    public function getInputAdditionalAttributes(): string
    {
        $inputAdditionalAttributes = $this->fieldAttributes
            ->additionalData[AdditionalFieldAttribute::INPUT_EXTRA_ATTRIBUTES->value] ?? [];

        return $this->prepareHtmlAttributes($inputAdditionalAttributes);
    }

    public function getWrapperAdditionalAttributes(): string
    {
        $wrapperAdditionalAttributes = $this->fieldAttributes
            ->additionalData[AdditionalFieldAttribute::WRAPPER_ELEM_EXTRA_ATTRIBUTES->value] ?? [];

        return $this->prepareHtmlAttributes($wrapperAdditionalAttributes);
    }

    public function getBeforeInputHtml(): string
    {
        return $this->fieldAttributes->additionalData[AdditionalFieldAttribute::BEFORE_INPUT_HTML->value] ?? '';
    }

    public function getAfterInputHtml(): string
    {
        return $this->fieldAttributes->additionalData[AdditionalFieldAttribute::AFTER_INPUT_HTML->value] ?? '';
    }

    public function getWrapperElemExtraClasses(): string
    {
        return $this->fieldAttributes->additionalData[AdditionalFieldAttribute::WRAPPER_ELEM_EXTRA_CLASS->value] ?? '';
    }

    private function prepareHtmlAttributes(?array $attributes): string
    {
        if (empty($attributes)) {
            return '';
        }

        return implode(' ', array_map(
            fn($attributeName, $attributeValue) =>
                sprintf('%s="%s"', $attributeName, htmlspecialchars($attributeValue, ENT_QUOTES, 'UTF-8')),
            array_keys($attributes),
            $attributes
        ));
    }
}
