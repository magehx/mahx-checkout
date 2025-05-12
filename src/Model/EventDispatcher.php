<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Rkt\MageData\Data;

class EventDispatcher
{
    public function __construct(private readonly ManagerInterface $eventManager)
    {
    }

    public function dispatchAddressFieldRendererSelected(array $eventData): void
    {
        $this->eventManager->dispatch('mahxcheckout_address_field_renderer_selected', $eventData);
    }

    public function dispatchPrepareAddressFieldRenderersBefore(array $eventData): void
    {
        $this->eventManager->dispatch('mahxcheckout_prepare_address_field_renderers_before', $eventData);
    }

    public function dispatchPrepareAddressFieldRenderersAfter(array $eventData): void
    {
        $this->eventManager->dispatch('mahxcheckout_prepare_address_field_renderers_after', $eventData);
    }

    public function dispatchAddressFormFieldsPrepared(array $eventData): DataObject
    {
        $transport =  new DataObject($eventData);
        $this->eventManager->dispatch('mahxcheckout_address_form_fields_prepared', ['transport' => $transport]);

        return $transport;
    }

    public function dispatchShippingAddressFormFieldsPrepared(array $eventData): DataObject
    {
        $transport =  new DataObject($eventData);
        $this->eventManager->dispatch('mahxcheckout_shipping_address_form_fields_prepared', ['transport' => $transport]);

        return $transport;
    }

    public function dispatchBillingAddressFormFieldsPrepared(array $eventData): void
    {
        $this->eventManager->dispatch('mahxcheckout_billing_address_form_fields_prepared', $eventData);
    }

    public function dispatchStepsDataBuildBefore(array $eventData): DataObject
    {
        $transport = new DataObject($eventData);
        $this->eventManager->dispatch('mahxcheckout_steps_data_build_before', ['transport' => $transport]);

        return $transport;
    }

    public function dispatchStepsDataBuildAfter(array $eventData): DataObject
    {
        $transport = new DataObject($eventData);
        $this->eventManager->dispatch('mahxcheckout_steps_data_build_after', ['transport' => $transport]);

        return $transport;
    }

    public function dispatchBillingAddressFieldRenderBefore(array $eventData): void
    {
        $this->eventManager->dispatch('mahxcheckout_billing_address_field_render_before', $eventData);
    }

    public function dispatchBillingAddressFieldRenderAfter(array $eventData): void
    {
        $this->eventManager->dispatch('mahxcheckout_billing_address_field_render_after', $eventData);
    }

    public function dispatchShippingAddressFieldRenderBefore(array $eventData): void
    {
        $this->eventManager->dispatch('mahxcheckout_shipping_address_field_render_before', $eventData);
    }

    public function dispatchShippingAddressFieldRenderAfter(array $eventData): void
    {
        $this->eventManager->dispatch('mahxcheckout_shipping_address_field_render_after', $eventData);
    }

    public function dispatchTotalsDataPrepareBefore(array $eventData): void
    {
        $this->eventManager->dispatch('mahxcheckout_totals_data_prepare_before', $eventData);
    }

    public function dispatchTotalsDataPrepareAfter(array $eventData): void
    {
        $this->eventManager->dispatch('mahxcheckout_totals_data_prepare_after', $eventData);
    }
}
