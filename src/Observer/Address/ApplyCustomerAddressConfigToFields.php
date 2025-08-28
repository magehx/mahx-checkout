<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\Address;

use MageHx\MahxCheckout\Data\FormField\Multiline\LineMeta;
use MageHx\MahxCheckout\Data\FormField\MultilineFieldMeta;
use MageHx\MahxCheckout\Data\FormField\SelectFieldMeta;
use MageHx\MahxCheckout\Data\FormFieldConfig;
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
        /** @var FormFieldConfig[] $addressFields */
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

    /**
     * @param FormFieldConfig[] $addressFields
     * @return void
     */
    private function modifyStreetField(array &$addressFields): void
    {
        $linesCount = $this->config->getStreetLinesCount();
        $streetConfigMap = $this->config->getAddressRenderMapping()['street'] ?? null;
        $streetLinesMap = array_values($streetConfigMap['lines'] ?? []);
        $linesMeta = [];

        for ($i = 0; $i < $linesCount; $i++) {
            $lineMap = $i === 0
                ? $streetConfigMap
                : ($streetLinesMap[$i - 1] ?? []);

            $label = $i === 0
                ? ($lineMap['label'] ?? 'Street Address')
                : ($lineMap['label'] ?? '');

            $linesMeta[] = new LineMeta(
                __($label)->render(),
                (bool)($lineMap['required'] ?? true),
                (int)($lineMap['width'] ?? 100)
            );
        }

        $addressFields['street']->meta = new MultilineFieldMeta($linesCount, $linesMeta);
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

        /** @var FormFieldConfig $field */
        $field = $addressFields[$fieldName];
        $field->required = $fieldConfig->show->isRequired();

        if (!empty($fieldConfig->options)) {
            $field->type = 'select';
            $field->meta = SelectFieldMeta::from(['optins' => $fieldConfig->getFieldOptions()]);
        }
    }
}
