# DTO (Data Transfer Objects)

---

> For full project context, return to [Main README](../README.md)
>
> [← Back to Controller Execution Model](../docs/CONTROLLER_EXECUTION_MODEL.md)

---

This document explains how **DTOs (Data Transfer Objects)** are used in Lapis Orkestra to enforce **structured, validated, and typed information transfer** across layers.

DTOs are a key mechanism supporting the thesis goals of:

* maintainability
* traceability
* reduction of implicit coupling
* safer incremental development

---

## Why DTOs exist

In many PHP systems, data moves through the application as loose arrays:

* request arrays
* decoded JSON arrays
* mixed associative arrays

This creates long-term maintenance problems:

* unclear contracts (“what keys exist?”)
* inconsistent types (“is this int or string?”)
* hidden coupling (“this action expects `foo_bar` key or it breaks”)
* hard-to-debug runtime errors

Lapis Orkestra uses DTOs to replace loose arrays with:

* explicit attributes
* strict data types
* documented meaning per attribute
* validation at construction time

---

## DTO Characteristics in Lapis Orkestra

Most DTOs in Lapis Orkestra:

* are final (or treated as value objects)
* have typed public properties (or typed getters)
* expose `fromArray()` as the primary constructor
* perform validation before accepting values

The outcome:

> **DTOs provide stable internal contracts and reduce ambiguity.**

---

## The `fromArray()` Pattern

A common DTO creation pattern is:

```php
$dto = SomeDto::fromArray($data);
```

### Responsibilities of `fromArray()`

* normalise input (if required)
* validate required keys
* validate data types
* validate allowed ranges/formats
* assign validated values to DTO attributes

This is intentionally different from:

* simply copying array values into properties

Validation must occur **before assignment**.

---

## Validation Approaches

Lapis Orkestra supports more than one validation approach for DTO creation.

### Approach A — Inline validation in `fromArray()`

Suitable for:

* small DTOs
* simple field rules

Example (conceptual):

```php
public static function fromArray(array $data): self
{
    if (!isset($data['id']) || !is_int($data['id'])) {
        throw new InvalidArgumentException('id must be an int');
    }

    $dto = new self();
    $dto->id = $data['id'];
    return $dto;
}
```

---

### Approach B — Factory + Validator

Used in the Security module for structured payload definitions.

Example in this codebase:

* DTO: `BitSynama\Lapis\Modules\Security\DTO\JwtPayloadDefinition`
* Factory: `BitSynama\Lapis\Modules\Security\Factories\JwtPayloadFactory`
* Validator: `BitSynama\Lapis\Modules\Security\Validators\Structure\JwtPayloadValidator`

#### Why use a factory + validator?

This approach is preferred when:

* input structure is complex
* validation rules are non-trivial
* validation must be reusable across multiple DTOs
* validation logic must be testable independently

#### Flow

```
Array input
  ↓
JwtPayloadFactory
  ↓
JwtPayloadValidator (structure verification)
  ↓
JwtPayloadDefinition (assignment)
```

This ensures the DTO only receives validated and verified values.

---

## ActionResponse DTO

A key DTO in the execution model is:

* `ActionResponse`

### Role

Controllers return `ActionResponse` instead of raw arrays.

`ActionResponse` typically represents:

* success/failure state
* status code
* message
* data payload
* error payload (if any)

This DTO is then consumed by:

* `Framework\Middlewares\FinalHandlerMiddleware`

which converts it into the appropriate response format for the dispatcher.

### Why this matters

* controllers stay consistent
* response formatting is centralised
* APIs and view rendering can share the same outcome structure

---

## Where DTOs are used

DTOs appear throughout Lapis Orkestra:

* request payload definitions
* security payloads (JWT claims)
* registry definitions (`RouteDefinition`, `ModuleDefinition`, `MenuItemDefinition`)
* action results (`ActionResponse`)

DTO usage provides a stable, typed backbone for the system.

---

## DTOs vs Checkers

DTOs and Checkers solve different problems:

* **Checkers** validate input and return boolean
* **DTOs** represent validated structured data

In practice:

* Checkers may validate raw input before DTO creation
* DTO creation performs final structure validation and assignment

---

## Maintainability Impact (Thesis rationale)

DTOs improve maintainability by:

* making contracts explicit
* preventing undocumented key dependencies
* reducing runtime type ambiguity
* enabling safer refactoring
* improving IDE support and static analysis

DTOs support incremental development:

* new fields can be introduced explicitly
* validation changes are localised
* older modules remain compatible with stable DTO contracts

---

## Recommended DTO Rules

To maintain consistent DTO quality:

1. Always prefer `fromArray()` or factory creation over direct property assignment
2. Validate before assignment
3. Fail fast with meaningful exceptions
4. Keep DTOs free from business logic
5. Use factories/validators for complex structures

---

## Next Document

Proceed to [Security](../docs/SECURITY.md) to read on:

* security placement in the request lifecycle
* JWT payload design, user types, and verification
* CSRF, headers, rate limiting
* incremental migration status (RBAC/verifiers)
  
---

[← Back to Controller Execution Model](../docs/CONTROLLER_EXECUTION_MODEL.md)

[← Back to Main README](../README.md)
