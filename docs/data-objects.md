# 🧩 Data Objects in MahxCheckout

MahxCheckout uses **Data Objects (DOs)** and **Data Transfer Objects (DTOs)** to structure and validate data across the entire checkout system — from **controllers** to **view models** to **templates**.

---

## 💡 Why Data Objects?

Data Objects give you a better way to represent and work with structured data.

Traditional Magento development leans on `Magento\Framework\DataObject`, which relies on `getData()` and `setData()` magic methods. While flexible, it's hard to reason about and doesn’t clearly express the shape of the data.

### What's Better About DOs?

* **Explicit**: You always know what properties are available.
* **Typed**: Properties use strict types, improving reliability.
* **Immutable by default**: Promotes safe, predictable data flow.
* **IDE-friendly**: Code completion and refactoring just work.
* **Great for templates**: Data passed to `.phtml` is clear and documented.

### ✅ Enabled by PHP 8+

Thanks to PHP 8+ features like:

* Constructor property promotion
* Union types
* Attributes (if used)
* Native type system

…it’s now easier and cleaner than ever to build robust data classes.

---

## 👤 Example: A Simple User Data Object

```php
class User
{
    public function __construct(
        public string $firstname,
        public string $lastname,
        public string $email,
        public ?string $middlename = '',
    ) {}
}
```

Here, the structure is immediately obvious. If you try to create a `User` without `firstname` or with a non-string `email`, PHP will throw a type error. That’s built-in validation for free.

But for **real-world** use cases, you’ll need **more than type safety**. You’ll want validation rules like “required”, “email format”, or even “must be one of these values”.

That’s where [`magento-data`](https://github.com/rajeev-k-tomy/magento-data) comes in.

---

Absolutely! Here's an updated version of the **"Advanced Validation with `magento-data`"** section with a clear explanation of the `rules()` method, its purpose, and how it integrates into validation. This adds helpful context for developers reading the MahxCheckout docs.

---

## ✅ Advanced Validation with `magento-data`

MahxCheckout integrates with [`rajeev-k-tomy/magento-data`](https://github.com/rajeev-k-tomy/magento-data) — a powerful package for defining **validation rules** directly inside your data objects.

Inspired by Spatie’s and Laravel’s DTOs, it brings modern data handling patterns into the Magento ecosystem.

---

### 📧 Example: `GuestEmailData`

```php
use Rkt\MageData\Data;

class GuestEmailData extends Data
{
    public function __construct(
        public string $email,
    ) {}

    public function rules(): array
    {
        return [
            'email' => 'required|email',
        ];
    }
}
```

---

#### 🔍 What is `rules()`?

The `rules()` method defines validation logic for each property in your data object. It returns an array where:

* The **key** is the property name (e.g., `'email'`)
* The **value** is a string (or array) of validation rules to apply

In the above example:

```php
'email' => 'required|email'
```

Means:

* `email` must be present (`required`)
* and it must be a valid email address (`email`)

---

#### ✅ Supported Rules

Rules are interpreted using [Rakit Validation](https://github.com/rakit/validation), so you get access to common rules like:

* `required`
* `email`
* `numeric`
* `min`, `max`, `between`
* `in`, `not_in`
* `date`, `url`, `boolean`
* `same`, `different`, `regex`, etc.

You can also define **custom rules** if needed.

---

## ⚙️ Controller Usage

MahxCheckout controllers consistently follow this pattern:

```php
public function execute(): ResultInterface
{
    $guestEmailData = GuestEmailData::from([
        'email' => $this->getRequest()->getParam('email'),
    ]);

    try {
        $guestEmailData->validate();
        // Passed to service layer
    } catch (\Exception $e) {
        // Handle validation errors
    }
}
```

* `from()` is a static constructor to instantiate a data object.
* `validate()` checks the rules defined in the `rules()` method
* After validation, the object is passed to the service layer

This provides a **standardized**, **declarative**, and **testable** approach to request validation.

---


## 🛠️ Instantiation: Why `new` is Perfectly Fine for Data Objects

In Magento, we typically avoid using `new` directly. Instead, we use **dependency injection (DI)** and **factories** — especially for services, models, or any class with dependencies. This is a Magento best practice to ensure testability, extensibility, and proper object lifecycle handling.

However, **Data Objects are a different story** — and here’s why:

---

### 💡 Data Objects are Simple Value Containers

Data Objects:

* Represent structured data (e.g., form submissions, DTOs)
* Are **stateless** (they hold values, not behavior)
* Don’t require Magento DI or configuration
* Are created from **dynamic input** (like `$_POST` or request parameters)

Because of this, using `new` or static constructors like `from()` is not just acceptable — it's the **right tool for the job**.

```php
$guestEmail = new GuestEmailData('test@example.com');
// or
$guestEmail = GuestEmailData::from(['email' => 'test@example.com']);
```

---

### ✅ Why This Is Safe and Common Practice

This pattern is widely accepted in the broader PHP ecosystem:

* In **Laravel**, you’ll find similar usage with `spatie/laravel-data`
* In **Symfony**, DTOs are created manually from request data
* Even in **PSR-oriented** codebases, static factory methods are common for immutable value objects

It avoids unnecessary factories and boilerplate for simple, one-off objects whose only job is to **hold and validate data**.

---


## 📦 Not Just for MahxCheckout

Although MahxCheckout uses this pattern extensively, the `magento-data` module is a **great fit for any Magento project**.

Use it to:

* Validate incoming request data
* Create clean, structured DTOs for services
* Improve the developer experience in templates
* Eliminate magic `getData()` calls

> 🧠 You'll find this approach especially helpful in form submissions, API handling, checkout customizations, and anywhere structured input needs to be validated and passed through the system.

---

## 🔚 Conclusion

MahxCheckout uses data objects not just for convenience — but to enforce a cleaner, safer, and more expressive architecture.

- ✅ Better structure
- ✅ Built-in validation
- ✅ Clean separation of concerns
- ✅ Stronger template support
- ✅ Reusable across Magento modules

We strongly recommend using [`magento-data`](https://github.com/rajeev-k-tomy/magento-data) beyond MahxCheckout in your own Magento projects.

> 💬 You can find more advanced features (nested DTOs, rule groups, serialization) in the module's documentation.

---
