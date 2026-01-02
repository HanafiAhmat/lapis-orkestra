# Controller Execution Model

---

> For full project context, return to [Main README](../README.md)
>
> [← Back to Request Lifecycle](../docs/REQUEST_LIFECYCLE.md)

---

This document explains **how business logic is executed** in Lapis Orkestra, and how responsibilities are deliberately separated between **Controllers, Verifiers, Actions, and Checkers**.

It formalises the execution model used throughout the framework and modules, and clarifies **when separation is required and when it is intentionally omitted**.

This model is central to the thesis focus on **maintainability and incremental development**.

---

## Core Principle

> **Controllers are orchestrators, not business logic containers.**

However, Lapis Orkestra does **not** force unnecessary abstraction.

If:

* business logic is trivial
* logic will not be reused
* separation adds more indirection than clarity

then Controllers **may contain inline logic**.

Separation into Checkers, Actions, and Verifiers is **recommended**, not mandatory.

This flexibility supports pragmatic, incremental development.

---

## Typical Execution Flow

The canonical execution flow is:

```
Request
  ↓
Middlewares
  ↓
Controller
  ↓
Verifier (access / RBAC check)
  ↓
Action (business logic)
  ↓
Checker (input validation inside Action)
  ↓
Controller
  ↓
FinalHandlerMiddleware
  ↓
Response Filters → HTTP Response
```

### Important Notes

* **Verifier runs immediately after Controller**

  * Access must be checked *before* business logic executes

* **Checker runs inside Action**

  * Validation is usually specific to the business use-case

* **Controller receives the Action result**

  * Usually as an array of mixed values or entity model

* **Final response conversion happens outside the Controller**

  * In `FinalHandlerMiddleware` where ActionResponse DTO returned from Controller will be converted to a response object


---

## Controller

### Role

Controllers are the **entry point for HTTP requests**.

Their responsibilities are:

* extract request parameters
* select the appropriate Verifier
* invoke the appropriate Action
* return an `ActionResponse`

Controllers **do not**:

* perform complex validation
* embed large business logic blocks
* enforce RBAC rules directly

---

### Inline Logic (Allowed)

Controllers may contain business logic **when all of the following apply**:

* logic is trivial
* logic is not reused elsewhere
* no complex validation is required

This prevents over-engineering and keeps the framework practical.

The base class:

```
BitSynama\Lapis\Framework\Controllers\AbstractController
```

demonstrates this approach by:

* providing helper methods
* allowing inline validation via Checkers
* returning structured responses

---

## Verifiers

### Role

Verifiers act as a **facade for access control and RBAC decisions**.

They exist to:

* decouple access logic from Controllers and Actions
* support multi-tenant role models
* improve testability of access rules

> Verifiers are intentionally placed **before Actions**.

---

### Multi-Tenant Design

Lapis Orkestra supports multi-tenant access by design:

* **Staff users** have their own role set
* **Customer users** have their own role set

Verifiers abstract these differences so Controllers and Actions do not need to know:

* which user type is active
* how roles are internally resolved

---

### Current Status

* Verifier architecture is present
* Full role enforcement is **not yet migrated** from the previous framework generation

This is explicitly documented as:

* an **incremental migration state**, not a design flaw

---

## Actions

### Role

Actions contain the **core business logic for a use-case**.

They are responsible for:

* executing domain rules
* coordinating entity models
* invoking Utilities and Services
* calling Checkers when validation is required

Actions are reusable across:

* HTTP Controllers
* CLI commands

---

### Entity Interaction

Most entity model interactions occur inside Actions:

* reading entities
* mutating entities
* persisting changes

Actions may return:

* entity objects
* arrays
* domain results

These results are then wrapped into an `ActionResponse` by the Controller.

---

## Checkers

### Role

Checkers perform **input validation only**.

They:

* validate structured input
* return boolean outcomes
* do not mutate state
* do not perform business logic

Checkers are intentionally simple and replaceable.

---

### Validator Flexibility

* Laminas Validator is currently used
* Developers may:

  * use a different library
  * write custom validation logic

Checkers exist primarily to:

* remove validation clutter from Controllers
* allow reuse across Actions

---

## ActionResponse DTO

### Role

Controllers return a final DTO:

```
ActionResponse
```

This DTO:

* encapsulates the outcome of a use-case
* standardises success / error payloads
* avoids returning loose arrays

---

### Response Conversion

`ActionResponse` is passed to:

```
BitSynama\Lapis\Framework\Middlewares\FinalHandlerMiddleware
```

This middleware:

* converts the DTO into the appropriate response format
* delegates final response emission to the Dispatcher

Controllers never emit raw HTTP responses directly.

---

## Why This Model Improves Maintainability

This execution model:

* keeps Controllers thin and readable
* localises business logic inside Actions
* isolates access logic in Verifiers
* keeps validation reusable and testable
* enables incremental refactoring

It also supports **gradual adoption**:

* simple endpoints can remain inline
* complex endpoints can be refactored progressively

---

## Summary

* Controllers orchestrate execution
* Verifiers guard access early
* Actions implement business logic
* Checkers validate input inside Actions
* Responses are standardised via DTOs and middleware

This layered execution model balances **architectural discipline** with **practical flexibility**, supporting long-term maintenance without forcing unnecessary abstraction.

---

## Next Document

Proceed to [DTO (Data Transfer Objects)](../docs/DTO.md) to read on:

* justify ActionResponse
* typed data contracts
* avoidance of loose arrays
  
---

[← Back to Request Lifecycle](../docs/REQUEST_LIFECYCLE.md)

[← Back to Main README](../README.md)
