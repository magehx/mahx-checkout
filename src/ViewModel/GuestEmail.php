<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

use MageHx\MahxCheckout\Data\GuestEmailData;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use MageHx\MahxCheckout\Model\FormDataStorage;
use MageHx\MahxCheckout\Model\QuoteDetails;

class GuestEmail implements ArgumentInterface
{
    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly Json $jsonSerializer,
        private readonly FormDataStorage $formDataStorage,
    ) {
    }

    public function getEmail(): string
    {
        return $this->formDataStorage->getData('email') ?? $this->quote->getQuoteCustomerEmail();
    }

    public function getValidationJson(): string
    {
        return $this->jsonSerializer->serialize(GuestEmailData::getValidationRules(['email' => $this->getEmail()]));
    }
}
