<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use Exception;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory as QuoteAddressFactory;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Psr\Log\LoggerInterface;
use MageHx\MahxCheckout\Data\ShippingEstimateFieldsData;
use MageHx\MahxCheckout\Model\Config;
use MageHx\MahxCheckout\Model\QuoteDetails;

class EstimateShippingMethodsService
{
    public function __construct(
        private readonly Config $config,
        private readonly QuoteDetails $quote,
        private readonly LoggerInterface $logger,
        private readonly QuoteAddressFactory $addressFactory,
        private readonly ShipmentEstimationInterface $shipmentEstimation,
        private readonly ShippingMethodManagementInterface $shippingMethodManagement,
    ) {}

    /**
     * @return ShippingMethodInterface[]
     */
    public function estimateByFields(ShippingEstimateFieldsData $estimateFieldsData): array
    {
        $address = $this->addressFactory->create();
        $address->setPostcode($estimateFieldsData->postcode)
            ->setCountryId($estimateFieldsData->country)
            ->setRegion($estimateFieldsData->region);

        return $this->estimateByAddress($address);
    }

    /**
     * @return ShippingMethodInterface[]
     */
    public function estimateByShippingAddress(): array
    {
        $address = $this->quote->getShippingAddress();

        $address->setCountryId($address->getCountryId() ?: $this->config->getDefaultShippingCountry());
        $address->setPostcode($address->getPostcode() ?: $this->config->getDefaultShippingPostcode());

        return $this->estimateByAddress($address);
    }

    /**
     * @return ShippingMethodInterface[]
     */
    public function estimateByAddressId(int $addressId): array
    {
        return $this->shippingMethodManagement->estimateByAddressId($this->quote->getId(), $addressId);
    }

    /**
     * @param AddressInterface $address
     * @return ShippingMethodInterface[]
     */
    public function estimateByAddress(AddressInterface $address): array
    {
        try {
            return $this->shipmentEstimation->estimateByExtendedAddress($this->quote->getId(), $address);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return [];
        }
    }
}
