<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model\FieldRenderer;

use Magento\Framework\DataObject;
use MageHx\MahxCheckout\Data\FormFieldConfig;
use MageHx\MahxCheckout\Model\FieldRenderer\Exception\NoAddressFieldRenderer;
use MageHx\MahxCheckout\Model\EventDispatcher;

class RendererPool
{
    public const DEFAULT_SORT_ORDER = 1000;

    public array $rendererList = [];

    public function __construct(
        private readonly EventDispatcher $eventDispatcher,
        private array $renderers = [],
    ) {
        $this->prepareRendererList();
    }

    /**
     * @throws NoAddressFieldRenderer
     */
    public function getRenderer(FormFieldConfig $fieldConfig): FieldRendererInterface
    {
        foreach ($this->rendererList as $renderer) {
            if ($renderer->canRender($fieldConfig)) {
                $rendererData = new DataObject(['renderer_list' => $this->rendererList, 'selected_renderer' => $renderer]);
                $this->eventDispatcher->dispatchAddressFieldRendererSelected([
                    'field_config' => $fieldConfig,
                    'form' => $fieldConfig->form,
                    'renderer_data' => $rendererData
                ]);
                return $rendererData->getData('selected_renderer');
            }
        }

        $this->throwRendererNotFoundException($fieldConfig);
    }

    private function prepareRendererList(): void
    {
        // Dispatch event BEFORE modifying renderers
        $this->eventDispatcher->dispatchPrepareAddressFieldRenderersBefore(
            ['renderers' => $this->renderers, 'rendererPool' => $this]
        );

        $this->normalizeRenderers()->sortRenderersByOrder();
        $this->rendererList = $this->prepareValidRendererList();

        // Dispatch event AFTER modification
        $this->eventDispatcher->dispatchPrepareAddressFieldRenderersAfter(
            ['renderers' => $this->renderers, 'rendererPool' => $this]
        );
    }

    private function throwRendererNotFoundException(FormFieldConfig $fieldAttributes)
    {
        $exception = new NoAddressFieldRenderer('No field renderer available for field: ' . $fieldAttributes->name);
        $exception->fieldAttributes = $fieldAttributes;

        throw $exception;
    }

    /**
     * Normalize renderer data: Ensure sort order exists and convert to integer
     */
    private function normalizeRenderers(): self
    {
        foreach ($this->renderers as &$renderer) {
            $renderer['sortOrder'] = (int) ($renderer['sortOrder'] ?? self::DEFAULT_SORT_ORDER);
        }

        return $this;
    }

    /**
     * Sort renderers by sortOrder
     */
    private function sortRenderersByOrder(): void
    {
        uasort($this->renderers, fn ($a, $b) => $a['sortOrder'] <=> $b['sortOrder']);

    }

    /**
     * Extract and return only valid renderers (implementing FieldRendererInterface)
     */
    private function prepareValidRendererList(): array
    {
        return array_map(
            fn ($renderer) => $renderer['class'],
            array_filter($this->renderers, fn ($renderer) => $renderer['class'] instanceof FieldRendererInterface)
        );
    }
}
