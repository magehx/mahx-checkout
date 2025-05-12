<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Data;

use Rkt\MageData\Data;

class GetRegionInputData extends Data
{
    public function __construct(
        public string $country,
        public string $form,
    ) {
    }

    public function rules(): array
    {
        return [
            'country' => 'required|min:2|max:2',
            'form' => 'required'
        ];
    }
}
