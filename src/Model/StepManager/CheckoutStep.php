<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\StepManager;

class CheckoutStep implements CheckoutStepInterface
{
    private string $name;
    private string $label;
    private string $urlHash;
    private string $stepLayoutHandle;
    private bool $isDefault;
    private ?string $saveDataUrl;
    private ?string $stepButtonLabel;

    /** @var FormComponentInterface[] */
    private array $components;

    public function __construct(
        string $name,
        string $label,
        string $urlHash,
        string $stepLayoutHandle,
        bool $isDefault = false,
        ?string $saveDataUrl = null,
        ?string $stepButtonLabel = null,
        array $components = []
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->urlHash = $urlHash;
        $this->stepLayoutHandle = $stepLayoutHandle;
        $this->isDefault = $isDefault;
        $this->saveDataUrl = $saveDataUrl;
        $this->stepButtonLabel = $stepButtonLabel;
        $this->components = $components;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function getUrlHash(): string
    {
        return $this->urlHash;
    }

    public function setUrlHash(string $urlHash): self
    {
        $this->urlHash = $urlHash;
        return $this;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    public function isValid(): bool
    {
        return true;
    }

    public function getSaveDataUrl(): ?string
    {
        return $this->saveDataUrl;
    }

    public function setSaveDataUrl(?string $url): self
    {
        $this->saveDataUrl = $url;
        return $this;
    }

    public function getButtonLabel(): ?string
    {
        return __($this->stepButtonLabel ?? 'Continue')->render();
    }

    public function setButtonLabel(?string $label): self
    {
        $this->stepButtonLabel = $label;
        return $this;
    }

    public function getLayoutHandle(): string
    {
        return $this->stepLayoutHandle;
    }

    public function setLayoutHandle(string $layoutHandle): self
    {
        $this->stepLayoutHandle = $layoutHandle;
        return $this;
    }

    public function getFormComponents(): array
    {
        return $this->components;
    }

    public function setFormComponents(array $components): self
    {
        $this->components = $components;
        return $this;
    }
}
