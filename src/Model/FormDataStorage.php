<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model;

class FormDataStorage
{
    private array $formData = [];

    public function setData(array $data): void
    {
        $this->formData = $data;
    }

    public function getData(string $key = null): mixed
    {
        if ($key) {
            return $this->isDataAvailableFor($key) ? $this->formData[$key] : null;
        }

        return  $this->formData;
    }

    public function isDataAvailableFor(string $key): bool
    {
        return isset($this->formData[$key]);
    }

    public function hasData(string $key): bool
    {
        return isset($this->formData[$key]);
    }
}
