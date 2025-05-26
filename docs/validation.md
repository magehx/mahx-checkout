# Validation in MahxCheckout

In any checkout system, data validation is crucial — both for ensuring data integrity and for providing a smooth user experience. Typically, validation is handled separately on the frontend and backend. This often leads to code duplication, inconsistency, and maintenance headaches.

MahxCheckout solves this by allowing you to define validation rules in one place — your **backend data objects** — and apply the same rules in the **frontend**. This unified approach saves time and reduces complexity.

---

## Why Unified Validation Matters

Traditionally, here's how validation works:

* **Backend**: Validate input using PHP (e.g., in controllers or service classes).
* **Frontend**: Write JavaScript validations, often duplicating backend logic.

This can easily go out of sync. MahxCheckout changes that by:

* Using a shared data object (DTO) that encapsulates both validation and data structure.
* Transporting validation rules to the frontend.
* Automatically applying rules to forms using JavaScript.

---

## Backend Validation

MahxCheckout uses the [`rajeev-k-tomy/magento-data`](https://github.com/rajeev-k-tomy/magento-data) package to define data objects with built-in validation capabilities. Under the hood, it uses the [`rakit/validation`](https://github.com/rakit/validation) library for flexible and expressive rule definitions.

### Example: Address Validation

```php
<?php
use Rkt\MageData\Data;

class AddressData extends Data
{
    public function __construct(
        public string $firstname,
        public string $lastname,
        public array $street,
        public string $city,
        public string $country_id,
        public string $postcode,
        public ?string $telephone = '',
        public ?string $region = '',
    ) {}

    public function rules(): array
    {
        return [
            'firstname' => 'required|alpha_spaces',
            'lastname' => 'required|alpha_spaces',
            'street.0' => 'required',
            'city' => 'required|alpha_spaces',
            'country_id' => 'required|max:2',
            'postcode' => 'required',
            'telephone' => 'required',
        ];
    }
}
```

### Validating in Controller

```php
<?php
use MageHx\MahxCheckout\Controller\Form\ComponentAction;

class AddressValidatePost extends HtmxAction
{
    public function __construct(
        Context $context,
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $addressData = AddressData::from($this->getRequest()->getParams());

        try {
            $addressData->validate();
            return $this->getComponentResponse('shipping.address.form');
        } catch (Exception $e) {
            $this->prepareErrorNotificationsWithFormData($addressData->toArray(), $e);
            return $this->getComponentResponse('shipping.address.form', withNotification: true);
        }
    }
}
```

If validation fails, catch the exception and return a structured error response.

### Customizing Validation

You can customize validation behavior by:

* Extending the base data object.
* Listening to validation events (`rkt/magento-data` supports multiple validation-related events).
* Using Magento plugins to override logic.
* Observing MahxCheckout-specific events.

---

## Frontend Validation

MahxCheckout uses the [JustValidate](https://just-validate.dev/) library for frontend form validation. What's special is that it **mirrors** the backend rules — so you only need to grasp these rules once.

### Basic Setup

```html
<form id="shipping-address" method="post" x-data="ShippingAddressForm">
  <input name="firstname" placeholder="First Name">
  <input name="lastname" placeholder="Last Name">
  <input name="street[]" placeholder="Street Line 1">
  <input name="street[]" placeholder="Street Line 2">
  <input name="city" placeholder="City">
  <select name="country_id"><option value="">Select Country</option></select>
  <input name="postcode" placeholder="Postcode">
  <input name="telephone" placeholder="Telephone">
  <button type="submit">Submit</button>
</form>
```

```js
<script>
  document.addEventListener('alpine:init', () => {
    Alpine.data('ShippingAddressForm', () => ({
      init() {
        window.mahxCheckout.validator({
          form: document.getElementById('shipping-address'),
          rules: {
            firstname: 'required|alpha_spaces',
            lastname: 'required|alpha_spaces',
            'street.0': 'required',
            city: 'required|alpha_spaces',
            country_id: 'required|max:2',
            postcode: 'required',
            telephone: 'required',
          },
        });
      }
    }));
  });
</script>
```

### Features

* **Live validation** before submit.
* **Error highlighting** and error messages.
* **Custom triggers**: validate manually via `validator.revalidate()`.
* **Field-level validation**: use `validator.revalidateField('firstname')`.

You can read more in [JustValidate documentation](https://just-validate.dev/).

---

## No Duplication: Sync Backend Rules to Frontend

Avoid repeating validation logic by passing backend rules directly to JavaScript:


### 🧠 ViewModel Example

To use backend validation rules on the frontend, you can convert them to JSON in a ViewModel:

```php
<?php
class ViewModel
{
    public function __construct(
        private \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {}

    public function getValidationJson(): string
    {
        // Static method to extract rules using example data
        return $this->serializer->serialize(
            AddressData::getValidationRules([
                'firstname' => '',
                'lastname' => '',
                'street' => [],
                'city' => '',
                'country_id' => '',
                'postcode' => '',
                'telephone' => '',
                'region' => '',
            ])
        );
    }
}
```

#### ➕ About `getValidationRules()`

* A static method that builds and returns the rule array from the payload given.
* Useful in templates where you don’t need a full object instance.

#### 🔁 Alternative

If you have an instance of the data object:

```php
$addressData = new AddressData(...);
$rules = $addressData->rules();
```

Both give you the same rule format. Use whichever fits your use case.

---

### Frontend Usage

```js
window.mahxCheckout.validator({
  form: document.getElementById('shipping-address'),
  rules: JSON.parse('<?= $viewModel->getValidationJson() ?>'),
});
```

This is the magic: **one definition — used everywhere.**

---

## Summary

MahxCheckout’s validation system:

* Centralizes rule definitions using `Data` objects.
* Reuses the same rules in both backend and frontend.
* Makes customization and maintenance simpler.
* Offers rich integration points using events and plugins.

---

### Technologies Used

* [rajeev-k-tomy/magento-data](https://github.com/rajeev-k-tomy/magento-data): Define data structures with validation in Magento.
* [rakit/validation](https://github.com/rakit/validation): Powerful backend validation engine.
* [JustValidate](https://just-validate.dev/): Lightweight frontend validation library.

---
