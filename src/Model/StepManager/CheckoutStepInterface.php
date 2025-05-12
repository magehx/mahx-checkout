<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\StepManager;

interface CheckoutStepInterface
{
    public function getName(): string;

    public function getLabel(): string;

    public function getUrlHash(): string;

    public function isDefault(): bool;

    public function isValid(): bool;

    public function getSaveDataUrl(): ?string;

    public function getButtonLabel(): ?string;

    public function getLayoutHandle(): string;

    /**
     * @return FormComponentInterface[]
     */
    public function getFormComponents(): array;
}
