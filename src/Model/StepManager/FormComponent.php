<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\StepManager;

class FormComponent implements FormComponentInterface
{
    /**
     * @param string $name This name must be used as the id of form element used in the frontend.
     * @param string $label
     */
    public function __construct(
        private readonly string $name,
        private readonly string $label
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
