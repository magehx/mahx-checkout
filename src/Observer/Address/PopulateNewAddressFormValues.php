<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\Address;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageHx\MahxCheckout\Data\AddressFieldAttributes;
use MageHx\MahxCheckout\Enum\AdditionalFieldAttribute;
use MageHx\MahxCheckout\Enum\CheckoutForm;
use MageHx\MahxCheckout\Model\Config;
use MageHx\MahxCheckout\Service\NewShippingAddressManager;
use MageHx\MahxCheckout\Service\PrepareRegionFieldAttribute;

/**
 * @event mahxcheckout_shipping_address_form_fields_prepared
 */
class PopulateNewAddressFormValues implements ObserverInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly NewShippingAddressManager $newShippingAddressManager,
        private readonly PrepareRegionFieldAttribute $prepareRegionFieldAttributeService,
    ) {}

    public function execute(Observer $observer): void
    {
        $transport = $observer->getData('transport');

        if (!$this->canProceed($observer)) {
            return;
        }

        /** @var AddressFieldAttributes[] $fields */
        $fields = $transport->getData('fields') ?? [];

        $this->populateFieldValues($fields);
        $this->addCountryFieldAttributes($fields);
        $this->addRegionFieldAttributes($fields);

        $transport->setData('fields', $fields);
    }

    private function canProceed(Observer $observer): bool
    {
        $transport = $observer->getData('transport');

        return $transport instanceof DataObject
            && $transport->getData('form_id') === CheckoutForm::NEW_ADDRESS->value
            && is_array($transport->getData('fields'));
    }

    private function populateFieldValues(array &$fields): void
    {
        $newAddress = $this->newShippingAddressManager->getNewAddress();

        if (!$newAddress) {
            $fields['country_id']->value = $this->config->getDefaultShippingCountry();
            return;
        }

        foreach ($fields as $field) {
            $fieldName = $field->name;

            $value = match ($fieldName) {
                'street'     => $newAddress->getStreet(),
                'country_id' => $newAddress->getCountryId() ?: $this->config->getDefaultShippingCountry(),
                'region'     => $newAddress->getRegionId() ?: $newAddress->getRegion() ?? '',
                default      => $newAddress->getData($fieldName) ?? '',
            };

            $field->value = $value;
        }
    }

    private function addCountryFieldAttributes(array &$fields): void
    {
        if (empty($fields['country_id'])) {
            return;
        }

        $fields['country_id']->additionalData[AdditionalFieldAttribute::INPUT_EXTRA_ATTRIBUTES->value] = [
            '@change' => 'handleCountryChange',
        ];
    }

    private function addRegionFieldAttributes(array &$fields): void
    {
        if (empty($fields['region'])) {
            return;
        }

        $regionField = $this->prepareRegionFieldAttributeService->execute(
            country: $fields['country_id']->value,
            regionField: $fields['region']
        );

        $fields['region'] = $this->prepareRegionFieldAttributeService->addAdditionalAttributesToRegion(
            regionField: $regionField,
            formId: CheckoutForm::NEW_ADDRESS->value
        );
    }
}
