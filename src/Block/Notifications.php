<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Block;

use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;

class Notifications extends Template
{
    private string|Phrase $errorMessage = '';
    private string|Phrase $successMessage = '';
    private array $validationErrors = [];

    public function setValidationErrors(array $errors): self
    {
        $this->validationErrors = $errors;
        return $this;
    }

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function setGenericError(string|Phrase $error): self
    {
        $this->errorMessage = $error;
        return $this;
    }

    public function getGenericErrorMessage(): string|Phrase
    {
        return $this->errorMessage;
    }

    public function setSuccessMessage(string|Phrase $message): self
    {
        $this->successMessage = $message;
        return $this;
    }

    public function getSuccessMessage(): string|Phrase
    {
        return $this->successMessage;
    }

    public function isHtmxOOBNeeded(): bool
    {
        return !empty($this->validationErrors) || !empty($this->successMessage) || !empty($this->errorMessage);
    }
}
