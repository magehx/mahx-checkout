<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\ViewModel;

use MageHx\MahxCheckout\Data\AddressData;
use MageHx\MahxCheckout\Data\ValidationMapperData;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use MageHx\MahxCheckout\Data\FormFieldConfig;
use MageHx\MahxCheckout\Enum\CheckoutForm;
use MageHx\MahxCheckout\Model\EventDispatcher;
use MageHx\MahxCheckout\Service\AddressFieldManager;

class ShippingAddress implements ArgumentInterface
{
    /**
     * @var FormFieldConfig[]
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

    public function renderField(FormFieldConfig $fieldConfig): string
    {
        $renderer = $this->addressFieldManager->getRenderForAddressField($fieldConfig);
        $rendererData = new DataObject(['renderer' => $renderer]);

        $this->eventDispatcher->dispatchShippingAddressFieldRenderBefore(
            ['field_config' => $fieldConfig, 'renderer_data' => $rendererData]
        );

        $fieldHtml = $rendererData->getData('renderer')->render($fieldConfig);
        $fieldHtmlDataObject = new DataObject(['html' => $fieldHtml]);

        $this->eventDispatcher->dispatchShippingAddressFieldRenderAfter(
            ['field_config' => $fieldConfig, 'field_html' => $fieldHtmlDataObject]
        );

        return $fieldHtmlDataObject->getData('html');
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
