<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model;

use Magento\Framework\DataObject;

class CheckoutDataStorage extends DataObject
{
    private bool $isErrorResponse = false;

    public function isErrorResponse(): bool
    {
        return $this->isErrorResponse;
    }

    public function setIsErrorResponse(bool $status): self
    {
        $this->isErrorResponse = $status;
        return $this;
    }
}
