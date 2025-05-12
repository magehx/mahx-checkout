<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use Magento\Customer\Model\Data\Address;
use Magento\Customer\Model\Data\Region;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;

class PrepareAddressLines
{
    public function __construct(
        private readonly Json $jsonSerializer,
        private readonly CustomerAddressService $customerAddressService,
    ) {
    }

    /**
     * @return string[]
     */
    public function getLinesOfAddress(DataObject|Address $address): array
    {
        $region = $address->getRegion();
        if ($region instanceof Region) {
            $region = $region->getRegion();
        }


        if ($address instanceof Address) {
            $country = $address->getCountryId();
        } else {
            $country = $address->getCountry();
        }

        return array_filter([
            'name' => $address->getFirstname() . ' ' . $address->getLastname(),
            'company' => $address->getCompany(),
            'line_1' => implode(', ', $address->getStreet()),
            'line_2' => implode(', ', array_filter([$address->getCity(), $region])),
            'postcode' => $address->getPostcode(),
            'country' => $country,
            'telephone' => $address->getTelephone(),
        ]);
    }

    public function getCurrentCustomerAddressLinesInfo(): array
    {
        $addressLinesList = [];

        foreach ($this->customerAddressService->getCurrentCustomerAddressList() as $address) {
            $id = (int) $address->getId();
            $addressLinesList["{$id}"] = [
                'addressId' => $id,
                'addressLines' => $this->getLinesOfAddress($address),
            ];
        }

        return $addressLinesList;
    }

    public function getCurrentCustomerAddressLinesInfoJson(): string
    {
        return $this->jsonSerializer->serialize($this->getCurrentCustomerAddressLinesInfo());
    }
}
