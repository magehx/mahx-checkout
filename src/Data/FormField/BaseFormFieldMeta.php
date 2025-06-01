<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data\FormField;

use MageHx\HtmxActions\Data\HxAttributesData;
use Rkt\MageData\Data;

class BaseFormFieldMeta extends Data
{
    public array $inputElementExtraAttributes = [];
    public string $beforeInputHtml = '';
    public string $afterInputHtml = '';
    public string $wrapperElemExtraClasses = '';
    public array $wrapperElemExtraAttributes = [];
    public ?HxAttributesData $inputElementHxAttributes = null;

    public function copyFrom(BaseFormFieldMeta $source): static
    {
        $this->inputElementExtraAttributes = $source->inputElementExtraAttributes;
        $this->beforeInputHtml = $source->beforeInputHtml;
        $this->afterInputHtml = $source->afterInputHtml;
        $this->wrapperElemExtraClasses = $source->wrapperElemExtraClasses;
        $this->wrapperElemExtraAttributes = $source->wrapperElemExtraAttributes;
        $this->inputElementHxAttributes = $source->inputElementHxAttributes;

        return $this;
    }
}
