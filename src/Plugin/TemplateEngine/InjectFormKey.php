<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Plugin\TemplateEngine;

use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\TemplateEngine\Php as TemplateEnginePhp;

class InjectFormKey
{
    public function __construct(
        private readonly FormKey $formKey,
        private readonly Escaper $escaper,
    ) {
    }

    public function beforeRender(
        TemplateEnginePhp $subject,
        BlockInterface $block,
        $fileName,
        array $dictionary = []
    ): array {
        // Add $formKey global variable to all templates
        if (!isset($dictionary['formKey'])) {
            $formKey = $this->formKey->getFormKey();
            $eFormKey = $this->escaper->escapeHtml($formKey);
            $dictionary['formKey'] = '<input type="hidden" name="form_key" value="' . $eFormKey . '" />';
        }

        return [$block, $fileName, $dictionary];
    }
}
