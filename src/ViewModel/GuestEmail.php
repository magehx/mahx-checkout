<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

use MageHx\MahxCheckout\Data\GuestEmailData;
use MageHx\MahxCheckout\Data\ValidationMapperData;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use MageHx\MahxCheckout\Model\CheckoutDataStorage;
use MageHx\MahxCheckout\Model\QuoteDetails;

class GuestEmail implements ArgumentInterface
{
    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly Json $jsonSerializer,
        private readonly CheckoutDataStorage $formDataStorage,
    ) {
    }

    public function getEmail(): string
    {
        return $this->getEmailData()->email;
    }

    public function getValidationJson(): string
    {
        $guestEmailData = $this->getEmailData();

        return $this->jsonSerializer->serialize(ValidationMapperData::from([
            'rules' => $guestEmailData->rules(),
            'messages' => $guestEmailData->messages(),
            'aliases' => $guestEmailData->aliases(),
        ])->exportToJs());
    }

    public function getEmailData(): GuestEmailData
    {
        return GuestEmailData::from([
            'email' => $this->formDataStorage->getData('email') ?? $this->quote->getQuoteCustomerEmail(),
        ]);
    }

    public function isVirtualCart(): bool
    {
        return $this->quote->isVirtualQuote();
    }
}
