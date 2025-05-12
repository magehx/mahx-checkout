<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\FieldRenderer\Exception;

use MageHx\MahxCheckout\Data\AddressFieldAttributes;

class NoAddressFieldRenderer extends \Exception
{
    public ?AddressFieldAttributes $fieldAttributes = null;
}
