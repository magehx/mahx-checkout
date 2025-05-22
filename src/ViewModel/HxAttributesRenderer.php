<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

use MageHx\HtmxActions\Enums\HtmxAdditionalAttributes;
use MageHx\HtmxActions\Enums\HtmxCoreAttributes;
use MageHx\HtmxActions\Enums\HtmxSwapOption;
use MageHx\MahxCheckout\Data\CheckoutHxAttributesData;

class HxAttributesRenderer extends \MageHx\HtmxActions\ViewModel\HxAttributesRenderer
{
    public function renderWithMainContentAttrs(array $hxAttributes): string
    {
        return $this->render([
            HtmxCoreAttributes::target->name => CheckoutHxAttributesData::TARGET_CHECKOUT_MAIN_CONTENT,
            HtmxCoreAttributes::swap->name => HtmxSwapOption::OUTER_HTML->value,
            HtmxAdditionalAttributes::indicator->name => CheckoutHxAttributesData::INDICATOR_PAGE_LOADER,
            ...$hxAttributes
        ]);
    }

    public function postWithMain(string $url): string
    {
        return $this->renderWithMainContentAttrs([HtmxCoreAttributes::post->name => $url]);
    }

    public function getWithMain(string $url): string
    {
        return $this->renderWithMainContentAttrs([HtmxCoreAttributes::get->name => $url]);
    }

    public function targetWithMain(string $selector): string
    {
        return $this->renderWithMainContentAttrs([HtmxCoreAttributes::target->name => $selector]);
    }

    public function swapWithMain(string $strategy): string
    {
        return $this->renderWithMainContentAttrs([HtmxCoreAttributes::swap->name => $strategy]);
    }

    public function triggerWithMain(string $event): string
    {
        return $this->renderWithMainContentAttrs([HtmxCoreAttributes::trigger->name => $event]);
    }

    public function indicatorWithMain(string $selector): string
    {
        return $this->renderWithMainContentAttrs([HtmxAdditionalAttributes::indicator->name => $selector]);
    }

    public function onWithMain(string $event, string $handler): string
    {
        return $this->renderWithMainContentAttrs([HtmxCoreAttributes::on->name => [$event => $handler]]);
    }
}
