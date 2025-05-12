<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model;

use Magento\Directory\Model\ResourceModel\Country\Collection as CountryCollection;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Directory\Model\ResourceModel\Region\Collection as RegionCollection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Directory\Helper\Data as DirectoryHelper;

class CountryProvider
{
    private array $allowedCountries = [];
    private array $regionsByCountry = [];

    public function __construct(
        private readonly DirectoryHelper $directoryHelper,
        private readonly CollectionFactory $countryCollectionFactory,
        private readonly RegionCollectionFactory $regionCollectionFactory
    ) {
    }

    /**
     * Get all allowed countries for a specific store.
     */
    public function getStoreAllowedCountries(int $storeId): CountryCollection
    {
        if (isset($this->allowedCountries[$storeId])) {
            return $this->allowedCountries[$storeId];
        }

        $allowedCountryCodes = $this->directoryHelper->getCountryCollection()->getAllIds();
        $this->allowedCountries[$storeId] = $this->countryCollectionFactory->create()
            ->addFieldToFilter('country_id', ['in' => $allowedCountryCodes])
            ->load();

        return $this->allowedCountries[$storeId];
    }

    /**
     * Get country options formatted as an associative array.
     */
    public function getStoreAllowedCountriesOption(int $storeId): array
    {
        $countryList = [];

        foreach ($this->getStoreAllowedCountries($storeId) as $country) {
            if ($country->getCountryId() && $country->getName()) {
                $countryList[$country->getCountryId()] = $country->getName();
            }
        }

        return $countryList;
    }

    /**
     * Get the store's default country.
     */
    public function getStoreDefaultCountry(): string
    {
        return $this->directoryHelper->getDefaultCountry();
    }

    /**
     * Get regions for a given country.
     */
    public function getRegionOptionsByCountry(string $countryId): array
    {
        $regionList = [];

        foreach ($this->getRegionCollectionByCountry($countryId) as $region) {
            $regionList[$region->getRegionId()] = $region->getName();
        }

        return $regionList;
    }

    public function getRegionCollectionByCountry(string $country): RegionCollection
    {
        if (isset($this->regionsByCountry[$country])) {
            return $this->regionsByCountry[$country];
        }

        $this->regionsByCountry[$country] = $this->regionCollectionFactory->create()
            ->addFieldToFilter('country_id', $country)
            ->setOrder('default_name', 'ASC')
            ->load();

        return $this->regionsByCountry[$country];
    }
}
