<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

use MageHx\MahxCheckout\Model\FormDataStorage;
use MageHx\MahxCheckout\Model\QuoteDetails;
use MageHx\MahxCheckout\Service\CustomerAddressService;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ShowBillingForm implements ArgumentInterface
{
    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly FormDataStorage $formDataStorage,
        private readonly CustomerAddressService $customerAddressService,
    ) {}

    public function canShowForm(): bool
    {
        if ($this->quote->isVirtualQuote()) {
            return true;
        }

        return (bool) $this->getShowFormValue();
    }

    public function canShowCards(): bool
    {
        return (bool)$this->getShowCardsValue();
    }

    public function isEditing(): bool
    {
        return $this->getShowCardsValue() || $this->getShowFormValue();
    }

    public function editFormRequestParams(): array
    {
        $customerHasAddress = (int)$this->customerAddressService->isCurrentCustomerHoldsAddress();

        return [
            'show_form' => $this->hasShowFormValue() ? $this->getShowFormValue() : (int)!$customerHasAddress ,
            'show_cards' => $this->hasShowCardsValue() ? $this->getShowCardsValue() : $customerHasAddress,
        ];
    }

    public function getShowFormValue(): int
    {
        return (int) ($this->formDataStorage->getData('show_form') ?? 0);
    }

    public function getShowCardsValue(): int
    {
        return (int) ($this->formDataStorage->getData('show_cards') ?? 0);
    }

    private function hasShowFormValue(): bool
    {
        return $this->formDataStorage->hasData('show_form');
    }

    private function hasShowCardsValue(): bool
    {
        return $this->formDataStorage->hasData('show_cards');
    }
}
