<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\Validation\AddressData;

use MageHx\MahxCheckout\Model\Config;
use Rakit\Validation\Rule;

class RegionRequiredRule extends Rule
{
    protected $implicit = true;
    protected $message = "The :attribute is required";

    public function __construct(
       private readonly Config $config,
    ) {
    }

    public function check($value): bool
    {
        $country = $this->validation->getValue('country_id');


        if (!in_array($country, $this->config->getRegionRequiredCountries())) {
            return true;
        }

        return (bool) $value;
    }
}
