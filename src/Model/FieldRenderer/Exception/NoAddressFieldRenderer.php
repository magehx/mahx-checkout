<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\FieldRenderer\Exception;

use MageHx\MahxCheckout\Data\FormFieldConfig;

class NoAddressFieldRenderer extends \Exception
{
    public ?FormFieldConfig $fieldAttributes = null;
}
