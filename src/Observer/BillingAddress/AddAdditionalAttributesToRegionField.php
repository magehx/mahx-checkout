<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Observer\BillingAddress;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageHx\MahxCheckout\Data\AddressFieldAttributes;
use MageHx\MahxCheckout\Service\PrepareRegionFieldAttribute;

class AddAdditionalAttributesToRegionField implements ObserverInterface
{
    public function __construct(
        private readonly PrepareRegionFieldAttribute $prepareRegionFieldAttributeService,
    ) {
    }

    public function execute(Observer $observer): void
    {
        /** @var AddressFieldAttributes $regionField */
        $fields = $observer->getData('fields');
        $regionField = $fields['region'] ?? null;

        if (!$regionField) {
            return;
        }

        $this->prepareRegionFieldAttributeService->addAdditionalAttributesToRegion($regionField, $regionField->form);
    }
}
