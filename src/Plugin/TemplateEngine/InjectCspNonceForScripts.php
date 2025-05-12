<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Plugin\TemplateEngine;

use Magento\Csp\Helper\CspNonceProvider;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\TemplateEngine\Php as TemplateEnginePhp;

class InjectCspNonceForScripts
{
    public function __construct(
        private readonly CspNonceProvider $cspNonceProvider,
    ) {
    }

    public function beforeRender(
        TemplateEnginePhp $subject,
        BlockInterface $block,
        $fileName,
        array $dictionary = []
    ): array {
        // Add $nonce global variable to all templates
        if (!isset($dictionary['nonce'])) {
            $nonce = $this->cspNonceProvider->generateNonce();
            $dictionary['nonce'] = $nonce;
        }

        return [$block, $fileName, $dictionary];
    }
}
