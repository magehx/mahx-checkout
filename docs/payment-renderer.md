# Payment Renderers

MahxCheckout handles payment method rendering at the **layout XML level**, making the integration of new payment methods **straightforward** for Magento 2 developers.

Each **payment renderer** is simply a block that is responsible for rendering a specific payment method’s UI and logic. Since this system is managed declaratively in layout XML, extending or overriding behavior feels native to Magento.

---

## How It Works

MahxCheckout defines a payment renderer container block inside `mahxcheckout_components.xml`:

```xml
<block class="MageHx\MahxCheckout\Block\PaymentMethods"
       name="payment.methods.form"
       template="MageHx_MahxCheckout::payment/payment_methods.phtml">

    <block name="payment.method.renderer.default"
           as="default"
           class="MageHx\MahxCheckout\Block\PaymentRenderer"
           template="MageHx_MahxCheckout::payment/renderer/default.phtml" />
</block>
```

* The outer block `payment.methods.form` is responsible for rendering the entire payment section.
* A `default` renderer is declared within it, and used if no custom renderer is found for a payment method.

---

## Adding a Custom Payment Renderer

To add a renderer for a specific payment method (e.g., `banktransfer`), define a new block inside the `payment.methods.form` block in your layout file:

**Example**: `mahxcheckout_step_payment.xml`

```xml
<referenceBlock name="payment.methods.form">
    <block
        name="payment.method.renderer.banktransfer"
        as="banktransfer"
        class="MageHx\MahxCheckout\Block\PaymentRenderer"
        template="MageHx_MahxCheckoutOffline::payment/renderer/default.phtml" />
</referenceBlock>
```

### Key Points:

* The `as` attribute **must match** the payment method code (`banktransfer` in this case).
* You must use or extend a block that implements `MageHx\MahxCheckout\Block\PaymentRendererBlockInterface`.
* In most cases, you can simply use the provided `PaymentRenderer` class.

💡 If you need custom logic, consider using a **ViewModel** rather than overriding the block. This keeps templates clean and testable.

---

## Validating Payment Method Forms

Some payment methods may introduce additional fields that require validation when the method is selected. MahxCheckout supports this by allowing you to associate a custom payment data object with your renderer block to handle the validation logic.

### Define the Data Object in Layout

```xml
<referenceBlock name="payment.methods.form">
    <block
        name="payment.method.renderer.purchaseorder"
        as="purchaseorder"
        class="MageHx\MahxCheckout\Block\PaymentRenderer"
        template="MageHx_MahxCheckoutOffline::payment/renderer/purchaseorder.phtml">
        <arguments>
            <argument name="data_class" xsi:type="string">
                \MageHx\MahxCheckoutOffline\Data\PurchaseOrderData
            </argument>
        </arguments>
    </block>
</referenceBlock>
```

### Define the Data Object Class

```php
<?php

use MageHx\MahxCheckout\Data\PaymentMethodData;

class PurchaseOrderData extends PaymentMethodData
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'additionalData.po_number' => 'required',
        ];
    }
}
```

* The custom data class must extend `PaymentMethodData`.
* Use the `rules()` method to define validation rules.
* These rules are automatically applied **on the frontend** when the corresponding payment method is selected.

If you do **not** specify a `data_class`, MahxCheckout will fallback to the default `PaymentMethodData`.

---

## Saving Payment Method

You can save the payment method in two ways:

### 1. Use the Built-in Controller

MahxCheckout provides a controller at:

```
mahxcheckout/form/paymentMethodPost
```

This is the default endpoint used by built-in renderers. It automatically processes fields under the `additionalData` array.

#### Example Form Markup

```html
<label>
    Purchase order number
    <input type="text"
           name="additionalData[po_number]"
           hx-trigger="change"
           hx-post="<?= $block->getUrl('mahxcheckout/form/paymentMethodPost') ?>" />
</label>
```

As long as your data is nested under `additionalData`, MahxCheckout will extract and save it automatically via the default controller.

### 2. Use a Custom Controller

If your payment logic is complex, you’re free to use a custom controller for saving payment method data. Just ensure the HTMX form posts to your controller path instead.

---

## Summary

* MahxCheckout uses **layout XML** to register payment renderers.
* Each renderer is tied to the **payment method code**.
* You can plug in your own renderer block and assign a **custom data class** for validation.
* Built-in validation and saving logic make integration smooth.
* Prefer **ViewModels** over complex blocks for rendering logic.

This approach keeps your payment integrations **modular**, **customizable**, and **aligned** with Magento 2 best practices.

---
