<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

use MageHx\MahxCheckout\Data\AddressData;
use MageHx\MahxCheckout\Data\ValidationMapperData;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use MageHx\MahxCheckout\Data\AddressFieldAttributes;
use MageHx\MahxCheckout\Enum\CheckoutForm;
use MageHx\MahxCheckout\Model\EventDispatcher;
use MageHx\MahxCheckout\Service\AddressFieldManager;

class ShippingAddress implements ArgumentInterface
{
    /**
     * @var AddressFieldAttributes[]
     */
    private ?array $fields = null;

    public function __construct(
        private readonly Json $jsonSerializer,
        private readonly EventDispatcher $eventDispatcher,
        private readonly AddressFieldManager $addressFieldManager,
    ) {
    }
    public function getAddressFields(string $formId = null): array
    {
        if ($this->fields !== null) {
            return $this->fields;
        }

        $formId = $formId ?? CheckoutForm::SHIPPING_ADDRESS->value;
        $this->fields = $this->addressFieldManager->getAddressFieldList($formId);

        $transportFields = $this->eventDispatcher
            ->dispatchShippingAddressFormFieldsPrepared(['fields' => $this->fields, 'form_id' => $formId]);
        $this->fields = $transportFields->getData('fields');

        return $this->fields;
    }

    public function renderField(AddressFieldAttributes $fieldAttributes): string
    {
        $renderer = $this->addressFieldManager->getRenderForAddressField($fieldAttributes);
        $rendererData = new DataObject(['renderer' => $renderer]);

        $this->eventDispatcher->dispatchShippingAddressFieldRenderBefore(
            ['field_attributes' => $fieldAttributes, 'renderer_data' => $rendererData]
        );

        $fieldHtml = $rendererData->getData('renderer')->render($fieldAttributes);
        $fieldHtmlDataObject = new DataObject(['html' => $fieldHtml]);

        $this->eventDispatcher->dispatchShippingAddressFieldRenderAfter(
            ['field_attributes' => $fieldAttributes, 'fieldHtml' => $fieldHtmlDataObject]
        );

        return $fieldHtmlDataObject->getData('html');
    }

    public function getAddressFieldsJson(): string
    {
        return $this->addressFieldManager->prepareAddressFieldsDataForJs($this->getAddressFields());
    }

    // @todo before after events to modify rules
    public function getValidationJson(): string
    {
        $addressData = AddressData::from([
            'firstname' => '',
            'lastname' => '',
            'street' => [],
            'city' => '',
            'country_id' => '',
            'postcode' => '',
            'telephone' => '',
            'region' => '',
        ]);
        return $this->jsonSerializer->serialize(ValidationMapperData::from([
            'rules' => $addressData->rules(),
            'messages' => $addressData->messages(),
            'aliases' => $addressData->aliases(),
        ])->exportToJs());
    }
}
