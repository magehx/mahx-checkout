<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\StepManager;

interface CheckoutStepInterface
{
    /**
     * Get the unique step name/identifier.
     */
    public function getName(): string;

    /**
     * Set the unique step name/identifier.
     */
    public function setName(string $name): self;

    /**
     * Get the step label shown in the UI.
     */
    public function getLabel(): string;

    /**
     * Set the step label.
     */
    public function setLabel(string $label): self;

    /**
     * Get the URL hash for navigation (e.g., "#shipping").
     */
    public function getUrlHash(): string;

    /**
     * Set the URL hash.
     */
    public function setUrlHash(string $urlHash): self;

    /**
     * Check if this step is the default active step.
     */
    public function isDefault(): bool;

    /**
     * Set whether this is the default step.
     */
    public function setIsDefault(bool $isDefault): self;

    /**
     * Check if this step is valid.
     */
    public function isValid(): bool;

    /**
     * Get the form submission URL, if any.
     */
    public function getSaveDataUrl(): ?string;

    /**
     * Set the form submission URL.
     */
    public function setSaveDataUrl(?string $url): self;

    /**
     * Get the step's button label.
     */
    public function getButtonLabel(): ?string;

    /**
     * Set the button label.
     */
    public function setButtonLabel(?string $label): self;

    /**
     * Get the layout handle used to render this step.
     */
    public function getLayoutHandle(): string;

    /**
     * Set the layout handle.
     */
    public function setLayoutHandle(string $layoutHandle): self;

    /**
     * Get the form components associated with this step.
     *
     * @return FormComponentInterface[]
     */
    public function getFormComponents(): array;

    /**
     * Set the form components.
     *
     * @param FormComponentInterface[] $components
     */
    public function setFormComponents(array $components): self;
}
