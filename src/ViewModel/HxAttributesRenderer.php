<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

use MageHx\HtmxActions\Enums\HtmxAdditionalAttributes;
use MageHx\HtmxActions\Enums\HtmxCoreAttributes;
use MageHx\HtmxActions\Enums\HtmxSwapOption;
use MageHx\MahxCheckout\Data\CheckoutHxAttributesData;

class HxAttributesRenderer extends \MageHx\HtmxActions\ViewModel\HxAttributesRenderer
{
    public function renderWithDefaults(array $hxAttributes): string
    {
        return $this->render([
            HtmxCoreAttributes::target->name => CheckoutHxAttributesData::TARGET_CHECKOUT_MAIN_CONTENT,
            HtmxCoreAttributes::swap->name => HtmxSwapOption::OUTER_HTML->value,
            HtmxAdditionalAttributes::indicator->name => CheckoutHxAttributesData::INDICATOR_PAGE_LOADER,
            ...$hxAttributes
        ]);
    }
}
