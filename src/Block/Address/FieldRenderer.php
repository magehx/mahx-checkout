<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Block\Address;

use MageHx\HtmxActions\Model\HxAttributeRender\HxAttributesRenderer;
use Magento\Framework\View\Element\Template;
use MageHx\MahxCheckout\Data\FormFieldConfig;

class FieldRenderer extends Template
{
    private ?FormFieldConfig $fieldConfig = null;

    protected $_template = 'MageHx_MahxCheckout::ui/address/fields/text.phtml';

    public function __construct(
        private readonly HxAttributesRenderer $hxAttributesRenderer,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function setFieldConfig(FormFieldConfig $fieldConfig): self
    {
        $this->fieldConfig = $fieldConfig;

        return $this;
    }

    public function getFieldConfig(): ?FormFieldConfig
    {
        return $this->fieldConfig;
    }

    public function getInputAdditionalAttributes(): string
    {
        $hxAttributes = $this->fieldConfig->meta->inputElementHxAttributes?->toArray() ?? [];

        return $this->prepareHtmlAttributes([
            ...$this->fieldConfig->meta->inputElementExtraAttributes,
            ...$this->hxAttributesRenderer->toArray($hxAttributes),
        ]);
    }

    public function getWrapperAdditionalAttributes(): string
    {
        return $this->prepareHtmlAttributes($this->fieldConfig->meta->wrapperElemExtraAttributes);
    }

    public function getBeforeInputHtml(): string
    {
        return $this->fieldConfig->meta->beforeInputHtml;
    }

    public function getAfterInputHtml(): string
    {
        return $this->fieldConfig->meta->afterInputHtml;
    }

    public function getWrapperElemExtraClasses(): string
    {
        return $this->fieldConfig->meta->wrapperElemExtraClasses;
    }

    private function prepareHtmlAttributes(?array $attributes): string
    {
        if (empty($attributes)) {
            return '';
        }

        $attributesHtml = '';

        foreach ($attributes as $attribute => $attributeValue) {
            $attributesHtml .= " {$attribute}=\"{$attributeValue}\"";
        }

        return trim($attributesHtml);
    }
}
