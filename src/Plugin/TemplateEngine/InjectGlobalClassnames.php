<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Plugin\TemplateEngine;

use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\TemplateEngine\Php as TemplateEnginePhp;
use MageHx\MahxCheckout\Helper\ClassNames;

class InjectGlobalClassnames
{
    public function __construct(private readonly ClassNames $classnames) {}

    public function beforeRender(TemplateEnginePhp $subject, BlockInterface $block, $fileName, array $dictionary = []): array
    {
        // Add $classNames global variable to all templates
        if (!isset($dictionary['classNames'])) {
            $dictionary['classNames'] = fn(array $classes) => $this->classnames->build($classes);
        }

        return [$block, $fileName, $dictionary];
    }
}
