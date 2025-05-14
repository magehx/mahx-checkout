<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use MageHx\MahxCheckout\Data\AddressFieldAttributes;
use MageHx\MahxCheckout\Enum\CheckoutForm;
use MageHx\MahxCheckout\Model\EventDispatcher;
use MageHx\MahxCheckout\Model\FormDataStorage;
use MageHx\MahxCheckout\Model\QuoteDetails;
use MageHx\MahxCheckout\Service\AddressFieldManager;
use MageHx\MahxCheckout\Service\PrepareAddressLines;

class BillingAddress implements ArgumentInterface
{
    private ?array $fields = null;

    public function __construct(
        private readonly QuoteDetails $quote,
        private readonly CustomerSession $customerSession,
        private readonly EventDispatcher $eventDispatcher,
        private readonly FormDataStorage $formDataStorage,
        private readonly AddressFieldManager $addressFieldManager,
        private readonly PrepareAddressLines $prepareAddressLinesService,
    ) {
    }

    public function isBillingSameAsShipping(): bool
    {
        return $this->quote->isBillingSameAsShipping();
    }

    public function getBillingSameAsShippingValue(): bool
    {
        return (bool)($this->formDataStorage->getData('is_billing_same') ?? $this->isBillingSameAsShipping());
    }

    public function isEditing(): bool
    {
        return (bool)$this->formDataStorage->getData('is_edit');
    }

    public function isCustomerLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    public function getBillingAddressLines(): array
    {
        $address = $this->isBillingSameAsShipping() ?
            $this->quote->getShippingAddress() : $this->quote->getBillingAddress();

        return $this->prepareAddressLinesService->getLinesOfAddress($address);
    }

    /**
    * @return AddressFieldAttributes[]
    */
    public function getAddressFields(): array
    {
        if ($this->fields !== null) {
            return $this->fields;
        }

        $this->fields = $this->addressFieldManager->getAddressFieldList(CheckoutForm::BILLING_ADDRESS->value);

        $this->eventDispatcher->dispatchBillingAddressFormFieldsPrepared(['fields' => $this->fields]);

        return $this->fields;
    }

    public function renderField(AddressFieldAttributes $fieldAttributes): string
    {
        $renderer = $this->addressFieldManager->getRenderForAddressField($fieldAttributes);
        $rendererData = new DataObject(['renderer' => $renderer]);

        $this->eventDispatcher->dispatchBillingAddressFieldRenderBefore(
            ['field_attributes' => $fieldAttributes, 'renderer_data' => $rendererData]
        );

        $fieldHtml = $rendererData->getData('renderer')->render($fieldAttributes);
        $fieldHtmlDataObject = new DataObject(['html' => $fieldHtml]);

        $this->eventDispatcher->dispatchBillingAddressFieldRenderAfter(
            ['field_attributes' => $fieldAttributes, 'fieldHtml' => $fieldHtmlDataObject]
        );

        return $fieldHtmlDataObject->getData('html');
    }

    public function getAddressFieldsJson(): string
    {
        return $this->addressFieldManager->prepareAddressFieldsDataForJs($this->getAddressFields());
    }

    public function canShowForm(): bool
    {
        return (bool)$this->formDataStorage->getData('show_form');
    }
}
