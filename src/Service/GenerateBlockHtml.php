<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;

class GenerateBlockHtml
{
    public function __construct(private readonly LayoutInterface $layout)
    {
    }

    public function getLoaderHtml(?string $loaderId = null, ?string $extraClass = null): string
    {
        $block = $this->layout->createBlock(Template::class);

        $block->setTemplate('MageHx_MahxCheckout::ui/common/section_loader.phtml');

        if ($loaderId) {
            $block->setData('id', $loaderId);
        }

        if ($extraClass) {
            $block->setData('extra_class', $extraClass);
        }

        return $block->toHtml();
    }
}
