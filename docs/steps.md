# Checkout Step System in MAHX Checkout

The **MAHX Checkout** module provides an extendable and modular **step system** to structure your checkout process. It's designed with flexibility in mind, allowing developers to easily customize or extend individual steps without rewriting the entire flow.

## ✨ Key Features

* ✅ Simple to add custom steps
* ✅ Easy to modify existing steps
* ✅ Clean architecture using Magento DI (Dependency Injection)
* ✅ Default implementation mimics Magento Luma checkout with two steps:

    * **Shipping**
    * **Payment**

---

## 🧱 Architecture Overview

### Step Configuration via `di.xml`

The checkout steps are configured using Magento's `di.xml`. Here's a simplified excerpt that shows how steps are registered:

```xml
<type name="MageHx\MahxCheckout\Model\StepManager\CheckoutStepPool">
    <arguments>
        <argument name="themeSteps" xsi:type="array">
            <item name="default" xsi:type="array">
                <item name="shipping" xsi:type="object">MageHx\MahxCheckout\Model\StepManager\Step\ShippingStepVirtual</item>
                <item name="payment" xsi:type="object">MageHx\MahxCheckout\Model\StepManager\Step\PaymentStepVirtual</item>
            </item>
        </argument>
    </arguments>
</type>
```

Steps are associated to a theme. Here we define two steps for the default theme.

Each step is defined as a **virtual type**. For example:

```xml
<virtualType name="MageHx\MahxCheckout\Model\StepManager\Step\ShippingStepVirtual"
             type="MageHx\MahxCheckout\Model\StepManager\CheckoutStep">
    <arguments>
        <argument name="name" xsi:type="string">shipping</argument>
        <argument name="label" xsi:type="string">Shipping</argument>
        <argument name="urlHash" xsi:type="string">shipping</argument>
        <argument name="isDefault" xsi:type="boolean">true</argument>
        <argument name="stepButtonLabel" xsi:type="string">Continue</argument>
        <argument name="stepLayoutHandle" xsi:type="string">mahxcheckout_step_shipping</argument>
        <argument name="saveDataUrl" xsi:type="string">mahxcheckout/shipping/saveShippingInformation</argument>
        <argument name="components" xsi:type="array">
            <item name="guest_email_form" xsi:type="object">GuestEmailForm</item>
            <item name="shipping_address_form" xsi:type="object">ShippingAddressForm</item>
            <item name="shipping_methods_form" xsi:type="object">ShippingMethodsForm</item>
        </argument>
    </arguments>
</virtualType>
```

You can go ahead an create a concrete step class and use it in the steps registration.
For simple purpose, we just leverage Magento DI's Virtual type feature here.

### 🔍 Key Step Properties

Each checkout step can be customized using the following properties:

| Property           | Description                                                              |
| ------------------ | ------------------------------------------------------------------------ |
| `name`             | Unique identifier of the step.                                           |
| `label`            | Step label shown in the step navigation bar.                             |
| `urlHash`          | The URL fragment (e.g., `#shipping`) used for direct access to the step. |
| `isDefault`        | Whether this step is the default (first) step.                           |
| `stepButtonLabel`  | Label of the navigation button (e.g., Continue, Place Order).            |
| `stepLayoutHandle` | Layout handle used to render the step's contents.                        |
| `saveDataUrl`      | URL endpoint used to persist data for the step.                          |
| `components`       | List of form components involved in the step.                            |

---

## 📦 Form Components

Each step is composed of multiple **FormComponents** that handle different parts of the UI (e.g., shipping address, email form). These are also defined via `di.xml` as virtual types:

```xml
<virtualType name="ShippingAddressForm" type="MageHx\MahxCheckout\Model\StepManager\FormComponent">
    <arguments>
        <argument name="name" xsi:type="string">shipping-address-form</argument>
        <argument name="label" xsi:type="string">Shipping Address</argument>
    </arguments>
</virtualType>
```

### Component Attributes

| Attribute | Description                                           |
| --------- | ----------------------------------------------------- |
| `name`    | Used as the `id` in the frontend — must be unique.    |
| `label`   | Displayed as the title of the form section in the UI. |

Each component extends:

* `MageHx\MahxCheckout\Model\StepManager\FormComponent`
* Implements: `FormComponentInterface`

---

## 🧩 How It Works: Step Registration & Pooling

The `CheckoutStepPool` class manages all steps:

* Instantiated via DI with all registered steps.
* Builds a collection of `MageHx\MahxCheckout\Data\CheckoutStepData` objects.
* Provides methods to fetch step data and navigation ordering.

Additional step data (beyond XML configuration):

| Property  | Description                                                                  |
| --------- | ---------------------------------------------------------------------------- |
| `order`   | Sort order of the step; used to determine navigation sequence.               |
| `isValid` | Backend validation flag for the step — true if all its components are valid. |

---

## 🧠 Extendability & Customization

### 🆕 Add a New Step

You can add new steps easily by adding another item in the `CheckoutStepPool` configuration and defining a virtual type with required properties.

### 🛠 Modify an Existing Step

Override the step’s virtual type in your module’s `di.xml` file to change its behavior or UI.

### 🧩 Modify Steps at Runtime (Observers)

Two helpful events are dispatched by the `CheckoutStepPool`:

| Event Name                             | Description                                                     |
| -------------------------------------- | --------------------------------------------------------------- |
| `mahxcheckout_steps_data_build_before` | Modify or add steps *before* the step data collection is built. |
| `mahxcheckout_steps_data_build_after`  | Modify step data *after* the collection is built.               |

These allow dynamic manipulation of the step list or its attributes.

---

## 🖼️ Template Files

The following PHTML templates control how steps are rendered in the frontend:

| File                    | Purpose                                                   |
| ----------------------- | --------------------------------------------------------- |
| `step_navigation.phtml` | Renders the top step navigation bar.                      |
| `page_action.phtml`     | Defines the step action button (Continue/Place Order).    |
| `navigation_js.phtml`   | Defines Alpine.js component that manages step navigation. |

---

## 🧪 Summary

MAHX Checkout’s step system is fully declarative and highly extensible:

* Add new steps by updating `di.xml`.
* Modify or override existing ones via DI or observers.
* Hook into the step building process using dispatched events.
* Easily manage and render steps using the provided ViewModels and templates.

This architecture ensures that your checkout flow remains modular, testable, and fully customizable without breaking the core logic.
