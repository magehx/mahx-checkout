<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\BillingAddress;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageHx\MahxCheckout\Data\FormFieldConfig;
use MageHx\MahxCheckout\Service\PrepareRegionFieldAttribute;

/**
 * @event mahxcheckout_billing_address_form_fields_prepared
 */
class AddAdditionalAttributesToRegionField implements ObserverInterface
{
    public function __construct(
        private readonly PrepareRegionFieldAttribute $prepareRegionFieldAttributeService,
    ) {
    }

    public function execute(Observer $observer): void
    {
        /** @var FormFieldConfig $regionField */
        $fields = $observer->getData('fields');
        $regionField = $fields['region'] ?? null;

        if (!$regionField) {
            return;
        }

        $this->prepareRegionFieldAttributeService->addAdditionalAttributesToRegion($regionField, $regionField->form);
    }
}
