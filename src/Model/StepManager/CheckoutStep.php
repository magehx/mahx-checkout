<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\StepManager;

class CheckoutStep implements CheckoutStepInterface
{
    /** @var FormComponentInterface[] */
    private array $components;

    public function __construct(
        private readonly string $name,
        private readonly string $label,
        private readonly string $urlHash,
        private readonly string $stepLayoutHandle,
        private readonly bool $isDefault = false,
        private readonly ?string $saveDataUrl = null,
        private readonly ?string $stepButtonLabel = null,
        array $components = []
    ) {
        $this->components = $components;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getUrlHash(): string
    {
        return $this->urlHash;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function isValid(): bool
    {
        return true;
    }

    public function getSaveDataUrl(): ?string
    {
        return $this->saveDataUrl;
    }

    public function getButtonLabel(): ?string
    {
        return __($this->stepButtonLabel ?? 'Continue')->render();
    }

    public function getLayoutHandle(): string
    {
        return $this->stepLayoutHandle;
    }

    public function getFormComponents(): array
    {
        return $this->components;
    }
}
