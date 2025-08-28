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

    public int $width = 100;

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

    public function getSpanWidth(): int
    {
        return match (true) {
            $this->width > 75 => 4,
            $this->width > 50 => 3,
            $this->width > 25 => 2,
            $this->width <= 25 => 1,
        };
    }
}
