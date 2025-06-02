# 📊 MAHX Checkout — Layouts

MAHX Checkout uses a bunch of layout XML files to organize different parts of the checkout page.
Each file groups related blocks together — making it easier to follow what’s going on, and way simpler to customize later.

Here’s a quick breakdown of each layout file, what it does, and which blocks or containers it sets up.

## 🗂️ Directory Structure

```text
view/frontend/layout/
├── mahxcheckout_index_index.xml
├── mahxcheckout_layout.xml
├── mahxcheckout_components.xml
├── mahxcheckout_scripts.xml
├── mahxcheckout_step_shipping.xml
├── mahxcheckout_step_shipping_customer_has_addresses.xml
├── mahxcheckout_step_shipping_customer_logged_in.xml
├── mahxcheckout_step_payment.xml
└── mahxcheckout_step_payment_customer_logged_in.xml
```

---

## 1. `mahxcheckout_index_index.xml`

**Handle:** Entry layout handle for the checkout.

**Triggered on:** Frontend route `/checkout` (internally `/mahxcheckout`)

### 🔍 Purpose
- Main page layout handle for the checkout route.
- Loads essential scripts and styles for the checkout.
- Removes default Luma checkout assets to avoid conflicts.
- Defines the entry block `main.phtml`, which initializes the Alpine/HTMX-based UI.
- Declares a `checkout.scripts` container for mounting Alpine components.
- Adds a page loader block.

---

## 2. `mahxcheckout_layout.xml`

**Handle:** Included via `mahxcheckout_index_index.xml`

**Purpose:** Core layout skeleton for the entire MAHX Checkout page.

### 🔍 Structure Highlights
- Mimics Luma checkout structure.
- Declares `checkout.step.navigation` — the top step indicator block.
- Defines `checkout.main.content` — the dynamic area for step specific components.
- Includes `checkout.right.section` — contains order summary, totals, etc.
- Adds `checkout.notifications` — used for inline validation/error messaging.

---

## 3. `mahxcheckout_components.xml`

**Handle:** Included via `mahxcheckout_layout.xml`

**Purpose:** Declares individual block components used in the checkout.

A "**component**" here represents a logical UI section.

Example: `shipping.address.form` — encapsulates the entire shipping address form.

These blocks are modular and reusable across different steps.

---

## 4. `mahxcheckout_scripts.xml`

**Handle:** Included via `mahxcheckout_index_index.xml`

**Purpose:** Registers all Alpine.js components used throughout the checkout.

### ✅ Why it's useful
- Centralizes all HTMX/Alpine scripts in one layout.
- Scripts load **once** and persist across dynamic swaps.
- Keeps JS organized and scoped to the checkout.

💡 When customizing MAHX Checkout, add your Alpine/HTMX components here to ensure they load consistently.

---

## 5. `mahxcheckout_step_shipping.xml`

**Handle:** Dynamically loaded when the **Shipping** step is active.

**Purpose:** Renders all block components related to the Shipping step.

### 📁 How it works
This is a **step-specific layout handle**. MAHX Checkout dynamically loads only the layout file for the current step to reduce overhead and complexity.

This layout ensures only shipping-related blocks are present when this step is active.

!!! Reference

    Checkout steps are registered in `app/etc/di.xml`.
    Layout handle mapping is part of the step configuration.

---

## 6. `mahxcheckout_step_payment.xml`

**Handle:** Dynamically loaded when the **Payment** step is active.

**Purpose:** Renders all block components related to the Payment step.

### 🔍 How it works
Same mechanism as the shipping step. When the checkout is in the **payment** phase, this layout injects relevant payment blocks (like methods, billing address, etc.), and removes unrelated content.

---

## 7. `mahxcheckout_step_shipping_customer_logged_in.xml`

**Handle:** Loaded only if the customer is **logged in** and for the **Shipping** step.

**Purpose:** Provides a variation of the shipping layout tailored for logged-in users.

### 🔍 How it works

MAHX Checkout dynamically includes additional handles for logged-in customers:

Suppose current layout handles include:

```text
- default
- mahxcheckout_step_shipping
```

If the user is logged in, Magento will also include:

```text
- default_customer_logged_in
- mahxcheckout_step_shipping_customer_logged_in
```

This allows:

  - Useful to modify customer logged-in checkout flow specific changes.
  - Guest flows to remain lightweight by excluding unnecessary components.

---


## 8. `mahxcheckout_step_payment_customer_logged_in.xml`

**Handle:** Loaded only if the customer is **logged in** and for the **Payment** step.

**Purpose:** Provides a variation of the payment layout tailored for logged-in users.

### 🔍 How it works

It is injected to the layout handle list similar to `mahxcheckout_step_shipping_customer_logged_in`.

This allows:

  - Logged-in flows to include billing address book features, saved addresses, etc.
  - Guest flows to remain lightweight by excluding unnecessary components.

---

## 9. `mahxcheckout_step_shipping_customer_has_addresses.xml`

**Handle:** Loaded only if the customer is **logged in**, holds **save addresses** and for the **Shipping** step.

**Purpose:** Provides customer address cards for the logged-in customer.

### 🔍 How it works

MAHX Checkout dynamically includes additional handles ending with `_customer_has_addresses` when customer is logged-in and has saved addresses:

Suppose current layout handles include:

```text
- default
- mahxcheckout_step_shipping
```

If the user is logged in, Magento will also include:

```text
- default_customer_has_addresses
- mahxcheckout_step_shipping_customer_has_addresses
```

This allows:

  - Logged-in flows to include shipping address book features, saved addresses, etc.
  - Guest flows to remain lightweight by excluding unnecessary components.

## 📌 Summary

- MAHX Checkout layout files follow a modular, step-based architecture.
- Each step (Shipping, Payment, etc.) has its own isolated layout handle.
- Shared structures like `scripts`, `components`, and `main layout` are split for clarity and reuse.
- Logged-in and guest flows are handled via handle suffixes (`_customer_logged_in`).

---
