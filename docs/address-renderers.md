# Address Field Renderers

MAHX Checkout uses a flexible system for rendering **Shipping Address** and **Billing Address** fields.
Each field is represented by an `MageHx\MahxCheckout\Data\AddressFieldAttributes` data object, allowing clean separation of **data**, **UI**, and **dynamic behavior**.

You can easily **customize** these fields using **Observers** or **Plugins** without modifying core ViewModels.

!!! tip
    Internally you find many observers that manipulates address fields. Do check them out and adapt the same approach as per your requirements.

---

## How Address Field Renderers Work

- Each address section (**Shipping** / **Billing**) has a **ViewModel** that prepares field definitions.
- Fields are prepared at `MageHx\MahxCheckout\Service\AddressFieldManager::getAddressFieldList($formId)`.
- Each of the fields will be passed to the `\MageHx\MahxCheckout\Model\FieldRenderer\RendererPool::getRenderer()` to find the responsible renderer for the field.
- Once renderer is determined, it will be used to render the html content for the field.
- Address field rendereres are configured via `di.xml` file. You can modify existing renderers or register new renderers via `di.xml` file.
- There are many built in events fired during field preparation and rendering of field etc. Developers can **inject**, **update**, or **override** fields dynamically by listening to these dispatched events.

!!! note
    You can find many examples of injecting **HTMX attributes**, setting **default country/region**, and **dynamic input type switching** in the module itself. Go through these observer implementations to understand how to customize the address fields.

---

## Some Useful Events

### Shipping Address Fields

| Property | Value |
|:--------|:------|
| ViewModel Class | `MageHx\MahxCheckout\ViewModel\ShippingAddress` |
| Prepare Fields Method | `getAddressFields()` |
| Prepare Fields Events | `mahxcheckout_address_form_fields_prepared`, `mahxcheckout_shipping_address_form_fields_prepared` |
| Field Render Events | `mahxcheckout_address_field_renderer_selected`, `mahxcheckout_shipping_address_field_render_before`, `mahxcheckout_shipping_address_field_render_after` |

---

### Billing Address Fields

| Property | Value |
|:--------|:------|
| ViewModel Class | `MageHx\MahxCheckout\ViewModel\BillingAddress` |
| Prepare Fields Method | `getAddressFields()` |
| Prepare Fields Events  | `mahxcheckout_address_form_fields_prepared`, `mahxcheckout_billing_address_form_fields_prepared` |
| Field Render Events | `mahxcheckout_address_field_renderer_selected`, `mahxcheckout_billing_address_field_render_before`, `mahxcheckout_billing_address_field_render_after` |

---

## Event List to Add/Modify Field and Field Renderers

The **event-driven system** allows you to modify, replace, or enhance fields dynamically.

| Event | Purpose | Example Observer |
|:------|:--------|:-----------------|
|`mahxcheckout_prepare_address_field_renderers_before`| Modify existing renderers or inject new renderers | No exmaples |
|`mahxcheckout_prepare_address_field_renderers_after`| Modify existing renderers or inject new renderers | No exmaples |
|`mahxcheckout_address_form_fields_prepared`| Add or modify fields applicable to both billing and shipping |[`AddCountryOptions`](https://github.com/magehx/mahx-checkout/blob/main/src/Observer/Address/AddCountryOptions.php)|
| `mahxcheckout_shipping_address_form_fields_prepared` | Add or modify shipping address fields | [`PopulateShippingAddressFormValues`](https://github.com/magehx/mahx-checkout/blob/main/src/Observer/ShippingAddress/PopulateShippingAddressFormValues.php) |
| `mahxcheckout_billing_address_form_fields_prepared` | Add or modify billing address fields | [`PopulateBillingAddressFormValues`](https://github.com/magehx/mahx-checkout/blob/main/src/Observer/BillingAddress/PopulateBillingAddressFormValues.php) |
| `mahxcheckout_address_field_renderer_selected` | Modify renderer or field data for both billing and shipping | [`UpdateRegionFieldBasedOnCountry`](https://github.com/magehx/mahx-checkout/blob/main/src/Observer/Address/UpdateRegionFieldBasedOnCountry.php) |
|`mahxcheckout_shipping_address_field_render_before`| Modify renderer or field data for shipping address | No exmaples |
|`mahxcheckout_shipping_address_field_render_after`| Modify rendered output for a field for shipping address | No exmaples |
|`mahxcheckout_billing_address_field_render_before`| Modify renderer or field data for shipping address | No exmaples |
|`mahxcheckout_billing_address_field_render_before`| Modify rendered output for a field for shipping address | No exmaples |

---

## Field Renderers

- Field renderers are registered via `di.xml` file. Example:

  ```
    <type name="MageHx\MahxCheckout\Model\FieldRenderer\RendererPool">
        <arguments>
            <argument name="renderers" xsi:type="array">
                <item name="Rkt_MahxCheckout::text" xsi:type="array">
                    <item name="class" xsi:type="object">MageHx\MahxCheckout\Model\FieldRenderer\Renderer\TextRenderer</item>
                </item>
                ...
                 </argument>
        </arguments>
    </type>
  ```

- Every renderer must implement `MageHx\MahxCheckout\Model\FieldRenderer\FieldRendererInterface`.

- This means every renderer should have below methods:

    - `render()` - This render the field and give back HTML of the field.
    - `canRender()` - Determines whether the renderer can be used to render the field.

- In MahxCheckout, these renderers basically give back html out of the block `MageHx\MahxCheckout\Block\Address\FieldRenderer`.

- Templates used are resides in `MageHx_MahxCheckout::ui/address/fields/*.phtml`.

## Address Field Attributes

- The attributes for an address field is represented using `MageHx\MahxCheckout\Data\AddressFieldAttributes` data object.
- It has:

  - `name` - Field name
  - `label` - Field label
  - `type` - Field type. eg: `text`, `select` etc.
  - `required` - Field is required or not.
  - `form` - Form (id) to which the field belongs to.
  - `value` - Field value
  - `rules` - Field validation rules.
  - `sortOrder` - Determines the position of the field in the form.
  - `additionalData` - Any special attributes for the field can be specified here.

    - Below is the list of additional data supported. You can find them in `MageHx\MahxCheckout\Enum\AdditionalFieldAttribute`.
    - `options` - Select field options. Must be array. Array key represent option value and Array value represents option label.
    - `defaultOptionLabel` - Select field default option label.
    - `inputElemAdditionalAttributes` - Input element additional attributes. For example `hx-*` attributes can be injected using this.
    - `beforeInputHtml` - This is inserted right after the wrapper `div` element of the field.
    - `afterInputHtml` - This is inserted at last inside the wrapper `div` element of the field.
    - `wrapperElemExtraClass` - Add extra classes to the wrapper `div` element of the field.
    - `wrapperElemAdditionalAttributes` - Add additional attributes to the wrapper `div` element of the field.

- In the event observers, you are basically manipulating these properties in order to change field's appearance to the frontend.

## Best Practices

- **Prefer Observers** over directly editing ViewModels.

---
