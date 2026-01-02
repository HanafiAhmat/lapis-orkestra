# Request Lifecycle

---

> For full project context, return to [Main README](../README.md)
>
> [← Back to Services](../docs/SERVICES.md)

---

This document describes the **request–response execution pipeline** in Lapis Orkestra.

It focuses on **everything that happens outside the controller action’s business logic**, and explains how the framework cleanly separates:

* *pre‑action guards and preparation* (Middlewares)
* *post‑action assurance and transformation* (ResponseFilters)

This separation is intentional and central to the framework’s goals of **maintainability**, **traceability**, and **incremental development**.

---

## Conceptual Overview

A request in Lapis Orkestra flows through a **well‑defined pipeline**:

```
Incoming Request
      ↓
Middleware Queue (Guards & Preparation)
      ↓
Controller Action (Business Logic)
      ↓
Response Filters (Quality Assurance)
      ↓
Response Emission
```

### Core principle

> **Business logic must not be polluted by cross‑cutting concerns.**

Anything that:

* validates, normalises, or blocks a request **before** business logic
* modifies, audits, or standardises a response **after** business logic

belongs **outside** the controller action.

---

## Execution Context

The lifecycle is orchestrated by:

* `Framework\\Kernel\\Dispatcher`

The dispatcher is responsible for:

1. Constructing the PSR‑7 request
2. Resolving the matched route
3. Building the middleware execution queue
4. Executing the controller action
5. Applying response filters
6. Emitting the final response

---

## Middlewares — Pre‑Action Guards

### Purpose

Middlewares act as **guards and preparatory layers** before a controller action executes.

They are responsible for:

* rejecting invalid or unauthorised requests
* normalising request data
* attaching context to the request
* enforcing technical or security constraints

> **Analogy:** Middlewares are security and process guards at the factory entrance.

---

### What Middlewares Must Do

* Validate request shape or headers
* Enforce authentication / authorisation prerequisites
* Parse request payloads (JSON, form‑encoded, method overrides)
* Attach request metadata (client IP, session, device, etc.)

---

### What Middlewares Must *Not* Do

* Contain business logic
* Modify the final response format
* Perform domain‑specific decisions

Violating these rules leads to poor maintainability and unpredictable behaviour.

---

### Middleware Registration

Middlewares are registered centrally via:

* `Framework\\Registries\\MiddlewareRegistry`

Registration maps a **string key** to a middleware class:

```php
Lapis::middlewareRegistry()->set(
    'core.security.csrf',
    CsrfMiddleware::class
);
```

This indirection:

* improves readability in route definitions
* allows swapping implementations without changing routes
* keeps middleware discovery traceable

---

### Middleware Selection

Middlewares are selected at runtime by:

* framework‑level defaults
* module‑defined route middleware
* application‑level route middleware

The dispatcher builds a **queue** of middleware instances **before** entering the controller action.

---

## Controller Action — Business Logic Boundary

The controller action represents the **only place** where business logic is executed.

Inside this boundary:

* **Actions** implement the use‑case logic
* **Checkers** validate input data
* **Verifiers** enforce access rules
* **DTOs** carry structured information

Everything else is deliberately excluded from this layer.

This strict boundary makes actions:

* easier to test in isolation
* easier to reason about
* easier to replace or refactor

---

## Response Filters — Post‑Action Quality Assurance

### Purpose

ResponseFilters operate **after** the controller action has completed.

They act as **quality assurance officers**, ensuring that responses leaving the system meet required standards.

> **Analogy:** ResponseFilters inspect and package the product before it is shipped to the customer.

---

### What ResponseFilters Do

* add or enforce HTTP headers
* apply response transformations
* strip debug or internal metadata
* standardise output formats
* apply security hardening (e.g. CSP headers)
* record audit or monitoring metadata

---

### What ResponseFilters Must *Not* Do

* perform business decisions
* access or mutate domain entities
* change the outcome of a use‑case

They operate strictly on the **response**, not on domain state.

---

### Response Filter Registration

Response filters are registered via:

* `Framework\\Registries\\ResponseFilterRegistry`

Example:

```php
Lapis::responseFilterRegistry()->set(
    'core.security.headers',
    SecurityHeadersResponseFilter::class
);
```

As with middlewares, filters are referenced by **keys**, not classes.

---

### Response Filter Selection

Response filters are selected based on:

* route definitions
* module defaults
* application overrides

They are executed **after** the controller action returns a response.

---

## Why Two Stages Instead of One?

Separating Middlewares and ResponseFilters avoids a common architectural mistake: a single, overloaded pipeline.

### Benefits

* Clear mental model: *before* vs *after* business logic
* Cleaner responsibilities
* Reduced accidental coupling
* Easier debugging and tracing

This separation directly supports **incremental evolution**:

* new guards can be added without touching business logic
* new response policies can be introduced without modifying controllers

---

## Traceability via Registries

Both Middlewares and ResponseFilters rely on registries:

* `MiddlewareRegistry`
* `ResponseFilterRegistry`

This ensures:

* discoverability of system behaviour
* centralised wiring
* reduced hidden coupling

A developer can inspect the registries to understand **exactly** what participates in the request lifecycle.

---

## Incremental Development Impact

Because guards and QA steps are externalised:

* modules can introduce new middlewares or filters independently
* features can evolve without refactoring existing actions
* legacy behaviour can be phased out gradually

This aligns with real‑world maintenance scenarios where systems must evolve without downtime.

---

## Summary

* Middlewares and ResponseFilters execute **outside** business logic
* Middlewares guard and prepare requests
* ResponseFilters assure and transform responses
* Both are registry‑driven and discoverable
* The controller action remains clean and focused

This execution model is a core mechanism by which Lapis Orkestra achieves maintainability and incremental growth.

---

## Next Document

Proceed to [Request Lifecycle](../docs/CONTROLLER_EXECUTION_MODEL.md) to read on:

* controller role
* action role
* checker role
* verifier role

---

[← Back to Services](../docs/SERVICES.md)

[← Back to Main README](../README.md)
