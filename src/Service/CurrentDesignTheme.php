<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Service;

use Magento\Framework\View\DesignInterface;

/**
 * Provides information about the current theme
 */
class CurrentDesignTheme
{
    /**
     * @var DesignInterface
     */
    protected $viewDesign;

    public function __construct(DesignInterface $viewDesign)
    {
        $this->viewDesign = $viewDesign;
    }

    /**
     * Returns true if the current theme is a Hyva theme, i.e. a descendant of Hyva/reset (or any Hyva namespaced theme)
     *
     * @return bool
     */
    public function isHyva(): bool
    {
        $theme = $this->viewDesign->getDesignTheme();
        while ($theme) {
            if (strpos($theme->getCode(), 'Hyva/') === 0) {
                return true;
            }
            $theme = $theme->getParentTheme();
        }
        return false;
    }
}
