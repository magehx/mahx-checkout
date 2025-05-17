<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Enum;

enum AddressAttributeShow: string
{
    case NO = '';
    case OPTIONAL = 'opt';
    case REQUIRED = 'req';

    public function canShow(): bool
    {
        return $this !== self::NO;
    }

    public function isRequired(): bool
    {
        return $this === self::REQUIRED;
    }
}
