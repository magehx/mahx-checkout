<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Plugin\TemplateEngine;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\TemplateEngine\Php as TemplateEnginePhp;

class InjectEscapeUtils
{

    public function __construct(private readonly Escaper $escaper)
    {
    }

    public function beforeRender(
        TemplateEnginePhp $subject,
        BlockInterface $block,
        $fileName,
        array $dictionary = []
    ): array {
        // Add $e global variable to all templates
        if (!isset($dictionary['e'])) {
            $dictionary['e'] = fn(string $value) => $this->escaper->escapeHtml($value);
        }
        // Add $eUrl global variable to all templates
        if (!isset($dictionary['eUrl'])) {
            $dictionary['eUrl'] = fn(string $value) => $this->escaper->escapeUrl($value);
        }
        // Add $eJs global variable to all templates
        if (!isset($dictionary['eJs'])) {
            $dictionary['eJs'] = fn(string $value) => $this->escaper->escapeJs($value);
        }
        // Add $eAttr global variable to all templates
        if (!isset($dictionary['eAttr'])) {
            $dictionary['eAttr'] = fn(string $value) => $this->escaper->escapeHtmlAttr($value);
        }

        return [$block, $fileName, $dictionary];
    }
}
