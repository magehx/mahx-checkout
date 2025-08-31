<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\Theme;

use MageHx\MahxCheckout\Data\CheckoutStepData;

interface CheckoutThemeInterface
{
    /**
     * Use only alphabets and underscore to represent theme code.
     *
     * @return string
     */
    public function getCode(): string;
    public function getLabel(): string;

    public function getParentCode(): ?string;

    /**
     * @return CheckoutStepData[]
     */
    public function getSteps(): array;

    public function findStepByName(string $name): ?CheckoutStepData;

    public function getInitialStep(): ?CheckoutStepData;

    public function getStepAfter(string|CheckoutStepData $step): ?CheckoutStepData;

    public function isLastStep(string|CheckoutStepData $step): bool;

    public function setParentThemes(array $parentThemes): self;

    /**
     * @return CheckoutThemeInterface[]
     */
    public function getParentThemes(): array;
}

