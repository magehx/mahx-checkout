<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use Magento\Customer\Model\Data\Address;
use Magento\Customer\Model\Data\Region;
use Magento\Framework\DataObject;

class PrepareAddressLines
{
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

        return [
            'name' => $address->getFirstname() . ' ' . $address->getLastname(),
            'company' => $address->getCompany(),
            'streetLine1' => implode(', ', $address->getStreet()),
            'streetLine2' => implode(', ', array_filter([$address->getCity(), $region])),
            'postcode' => $address->getPostcode(),
            'country' => $country,
            'telephone' => $address->getTelephone(),
            'region' => $region,
            'city' => $address->getCity(),
        ];
    }
}
