<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\FieldRenderer\Renderer;

use Magento\Framework\View\LayoutInterface;
use MageHx\MahxCheckout\Block\Address\FieldRenderer;
use MageHx\MahxCheckout\Data\AddressFieldAttributes;
use MageHx\MahxCheckout\Model\FieldRenderer\FieldRendererInterface;

class SelectRenderer implements FieldRendererInterface
{
    public function __construct(private readonly LayoutInterface $layout)
    {
    }

    public function render(AddressFieldAttributes $attributes): string
    {
        /** @var FieldRenderer $block */
        $block = $this->layout->createBlock(FieldRenderer::class)
            ->setTemplate('MageHx_MahxCheckout::ui/address/fields/select.phtml');
        $block->setFieldAttributes($attributes);

        return $block->toHtml();
    }

    public function canRender(AddressFieldAttributes $attributes): bool
    {
        return $attributes->type === 'select';
    }
}
