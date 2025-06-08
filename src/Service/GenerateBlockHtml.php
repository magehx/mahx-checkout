<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\LayoutFactory;

class GenerateBlockHtml
{
    public function __construct(
        private readonly LayoutFactory $layoutFactory,
        private readonly LayoutInterface $layout,
    ){
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

    public function getComponentHtml(string $componentName, bool $withHtmxOob = false): string
    {
        $layout = $this->loadLayoutFromHandles(['mahxcheckout_components']);
        $block = $layout->getBlock($componentName);

        if ($withHtmxOob) {
            $block?->setData('is_htmx_oob', true);
        }

        return $block->toHtml();
    }

    private function loadLayoutFromHandles(array $handles): LayoutInterface
    {
        $layout = $this->getLayout();

        foreach ($handles as $handle) {
            $layout->getUpdate()->addHandle($handle);
        }

        $layout->getUpdate()->load();
        $layout->generateXml();
        $layout->generateElements();

        return $layout;
    }

    private function getLayout(): LayoutInterface
    {
        $layoutResult = $this->layoutFactory->create();
        return $layoutResult->getLayout();
    }
}
