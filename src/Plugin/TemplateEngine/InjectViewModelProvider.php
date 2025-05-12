<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Plugin\TemplateEngine;

use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\TemplateEngine\Php as TemplateEnginePhp;
use MageHx\MahxCheckout\Model\ViewModelProvider;

class InjectViewModelProvider
{
    public function __construct(private readonly ViewModelProvider $viewModelProvider) {}

    public function beforeRender(
        TemplateEnginePhp $subject,
        BlockInterface $block,
        $fileName,
        array $dictionary = []
    ): array {
        // Add $viewModelProvider global variable to all templates
        if (!isset($dictionary['viewModelProvider'])) {
            $dictionary['viewModelProvider'] = fn(string $viewModelName) =>
                $this->viewModelProvider->get($viewModelName);
        }

        return [$block, $fileName, $dictionary];
    }
}
