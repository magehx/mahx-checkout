<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\StepManager;

interface FormComponentInterface
{
    public function getName(): string;

    public function getLabel(): string;
}
