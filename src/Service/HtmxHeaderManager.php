<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use MageHx\HtmxActions\Enums\HtmxResponseHeader;
use MageHx\HtmxActions\Enums\HtmxSwapOption;
use MageHx\HtmxActions\Service\HtmxHeaderReader;
use Magento\Framework\Controller\ResultInterface;

class HtmxHeaderManager
{
    public function __construct(
        private readonly HtmxHeaderReader $htmxHeaderReader,
        private readonly StepSessionManager $stepSessionManager,
    ) {
    }

    public function getCheckoutControllerName(): string
    {
        return str_contains($this->htmxHeaderReader->getOriginUrl(), 'mahxcheckout') ? 'mahxcheckout' : 'checkout';
    }

    public function setResponseUrlWithCurrentStep(ResultInterface $response): ResultInterface
    {
        $step = $this->stepSessionManager->getStepData();
        $controller = $this->getCheckoutControllerName();

        return $this->htmxHeaderReader->setResponsePushUrl($response, "/{$controller}/#{$step->urlHash}");
    }

    public function setResponseWithNoReSwap(ResultInterface $response): ResultInterface
    {
        return $this->htmxHeaderReader->setResponseHeader(
            $response,
            HtmxResponseHeader::RESWAP,
            HtmxSwapOption::none->value
        );
    }

    public function getReader(): HtmxHeaderReader
    {
        return $this->htmxHeaderReader;
    }
}
