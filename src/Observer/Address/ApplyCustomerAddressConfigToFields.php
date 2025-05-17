<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\Address;

use MageHx\MahxCheckout\Data\AddressFieldAttributes;
use MageHx\MahxCheckout\Enum\AdditionalFieldAttribute as AFA;
use MageHx\MahxCheckout\Enum\YesNo;
use MageHx\MahxCheckout\Model\Config;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use LogicException;
use Throwable;

/**
 * Applies customer configuration settings to address field definitions at runtime.
 * Settings you can find at: Stores > Configuration > Customers > Customer Configuration > Name and Address Options.
 */
class ApplyCustomerAddressConfigToFields implements ObserverInterface
{
    public function __construct(
        private readonly Config $config,
    ) {}

    /**
     * Modify address field attributes based on system configuration.
     */
    public function execute(Observer $observer): void
    {
        /** @var AddressFieldAttributes[] $addressFields */
        $addressFields = $observer->getData('transport')->getData('fields');

        try {
            $this->modifyStreetField($addressFields);
            $this->modifyTelephoneField($addressFields);
            $this->modifyPrefixField($addressFields);
            $this->modifyMiddleNameField($addressFields);
            $this->modifySuffixField($addressFields);

            $observer->getData('transport')->setData('fields', $addressFields);
        } catch (Throwable) {
            // Silent fail to avoid breaking checkout experience.
            return;
        }
    }

    private function modifyStreetField(array &$addressFields): void
    {
        $addressFields['street']->additionalData[AFA::MULTILINE_COUNT->value] = $this->config->getStreetLinesCount();
    }

    private function modifyTelephoneField(array &$addressFields): void
    {
        if (!isset($addressFields['telephone'])) {
            return;
        }

        $config = $this->config->getTelephoneShow();

        if (!$config->canShow()) {
            unset($addressFields['telephone']);
            return;
        }

        $addressFields['telephone']->required = $config->isRequired();
    }

    private function modifyPrefixField(array &$addressFields): void
    {
        if (isset($addressFields['prefix'])) {
            $this->applyNameFieldConfig('prefix', $addressFields);
        }
    }

    private function modifyMiddleNameField(array &$addressFields): void
    {
        if ($this->config->getMiddleNameShow() === YesNo::NO) {
            unset($addressFields['middlename']);
        }
    }

    private function modifySuffixField(array &$addressFields): void
    {
        if (isset($addressFields['suffix'])) {
            $this->applyNameFieldConfig('suffix', $addressFields);
        }
    }

    private function applyNameFieldConfig(string $fieldName, array &$addressFields): void
    {
        $fieldConfig = match ($fieldName) {
            'prefix' => $this->config->getPrefixConfig(),
            'suffix' => $this->config->getSuffixConfig(),
            default => throw new LogicException("Unsupported field '{$fieldName}' in name field config"),
        };

        if (!$fieldConfig->show->canShow()) {
            unset($addressFields[$fieldName]);
            return;
        }

        $field = $addressFields[$fieldName];
        $field->required = $fieldConfig->show->isRequired();

        if (!empty($fieldConfig->options)) {
            $field->type = 'select';
            $field->additionalData[AFA::OPTIONS->value] = $fieldConfig->getFieldOptions();
        }
    }
}
