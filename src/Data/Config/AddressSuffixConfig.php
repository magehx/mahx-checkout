<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data\Config;

use MageHx\MahxCheckout\Enum\AddressAttributeShow;
use Rkt\MageData\Data;

class AddressSuffixConfig extends Data
{
    public function __construct(
        public string $options = '',
        public AddressAttributeShow $show = AddressAttributeShow::NO,
    ) {
    }

    public function getFieldOptions(): array
    {
        $options = array_map('trim', array_filter(explode(';', $this->options)));

        return array_combine($options, $options);
    }
}
