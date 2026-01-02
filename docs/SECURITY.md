# Security

---

> For full project context, return to [Main README](../README.md)
> [← Back to DTO (Data Transfer Objects) README](../docs/DTO.md)

---

This document describes how **security is designed and applied** in Lapis Orkestra.

Security in Lapis Orkestra is intentionally **layered**. Instead of centralising all security concerns into a single “security service”, the framework distributes security responsibilities across:

* request lifecycle guards (Middlewares)
* response assurance (ResponseFilters)
* module boundaries (Interactors)
* typed payload definitions (DTOs)
* controlled global state (Registries)
* adapter/provider isolation (Utilities / Services)

This design supports the thesis focus on **maintainability and incremental development**, because security features can be introduced, upgraded, or replaced without rewriting unrelated application code.

---

## Project Status

⚠️ **Important Notice**

The security module and security layers are partially migrated from a previous framework generation.

* ✅ Key infrastructure pieces exist and are integrated into the boot and dispatch pipeline
* ✅ JWT payload structure, validation, and core security DTO patterns are present
* ✅ Some security middleware exists in the Security module
* ⏳ Not all security features were migrated due to time constraints
* ⏳ Role enforcement via Verifiers (RBAC facade) is not yet fully implemented

This is treated as:

> an **incremental migration state**, not a design flaw.

---

## Security Boundary Placement

Security concerns are applied at multiple points in the request lifecycle:

```
Request
  ↓
Security Middlewares (guards)
  ↓
Controller
  ↓
Verifier (RBAC / access check)
  ↓
Action (business logic)
  ↓
ResponseFilters (security headers / response assurance)
  ↓
Emit
```

This layered placement ensures:

* invalid or malicious requests are rejected early
* business logic executes only after prerequisite checks
* outbound responses meet safety requirements

---

## Security Mechanisms Overview

The framework’s security strategy includes:

* JWT-based authentication
* structured JWT payload validation (DTO + Validator)
* multi-user-type design (Staff vs Customer)
* CSRF protection for cookie/session contexts
* secure cookie defaults (HttpOnly, SameSite)
* response security headers (CSP, X-Frame-Options, etc.)
* audit logging hooks (optional)
* rate limiting / throttling (planned or partial)

---

## Authentication

### JWT as the core token format

Lapis Orkestra uses JWT as the primary authentication mechanism.

JWT handling is designed to remain maintainable by:

* isolating parsing/encoding behind the Security module
* using typed DTOs for payload contracts
* validating structure before trusting values

---

### JWT Payload Definition

JWT payloads are represented as a DTO:

* `BitSynama\Lapis\Modules\Security\DTO\JwtPayloadDefinition`

Instead of treating JWT claims as loose arrays, the payload is defined as structured data with explicit meaning and types.

---

### Factory + Validator pattern

JWT payload construction uses:

* `BitSynama\Lapis\Modules\Security\Factories\JwtPayloadFactory`

which verifies input using:

* `BitSynama\Lapis\Modules\Security\Validators\Structure\JwtPayloadValidator`

This ensures:

* claims are present and valid
* required types are enforced
* payload contracts remain stable

> This is a key maintainability mechanism: JWT usage becomes contract-driven, not array-driven.

---

## Multi-User-Type Security Model

Lapis Orkestra supports multiple user types by design.

Typical built-in types:

* **Staff** user (administrative tenant)
* **Customer** user (public tenant)

This distinction affects:

* authentication resolution
* permissions and role checks
* routing separation (admin vs public)

The Security module integrates with registries such as:

* `UserTypeRegistry`

so that user-type behaviour remains configurable and discoverable.

---

## Authorisation (RBAC via Verifiers)

### Why Verifiers exist

In the previous framework generation, RBAC checks embedded directly in controllers/actions created testing issues.

Lapis Orkestra introduces **Verifiers** as a facade layer to:

* centralise access control logic
* provide a predictable seam for unit testing
* isolate role model differences between Staff and Customer tenants

### Current status

* Verifier architecture exists
* full role enforcement is **not fully migrated**

This is documented as part of incremental development.

---

## Security Middlewares (Guards)

Security is primarily enforced early through middlewares.

Middlewares are:

* registered via `MiddlewareRegistry`
* referenced by key in route definitions
* executed before controller actions

### Examples of security middleware responsibilities

Depending on implementation state, security middlewares may include:

* authentication middleware (require valid JWT)
* CSRF middleware (cookie contexts)
* throttling middleware (login attempt control)
* request signature validation (optional)
* user type availability middleware (ensures correct tenant context)

> Some of these exist in the Security module but not all are fully migrated.

---

## CSRF Protection

CSRF protection is relevant in contexts where:

* authentication tokens are stored in cookies
* session-based state exists

Lapis Orkestra supports CSRF as:

* middleware-based enforcement
* token acquisition through a dedicated endpoint (design goal)

This keeps CSRF logic outside Actions and Controllers.

---

## Secure Cookies

Cookie behaviour is centralised under:

* `CookieUtility`

Configuration supports:

* `secure`
* `httponly`
* `samesite`
* `domain`
* `path`

Centralising cookie policy improves maintainability and prevents inconsistent security defaults.

---

## Response Security (Headers & Assurance)

Security is also applied at the **response stage**.

ResponseFilters can enforce:

* `Content-Security-Policy`
* `X-Frame-Options`
* `X-Content-Type-Options`
* `Referrer-Policy`
* caching policy headers

This approach ensures:

* business logic does not need to manage security headers
* output policies remain consistent across modules

---

## Audit Logging (Optional)

Critical actions may be recorded via an audit mechanism.

Audit logging can be:

* toggled by configuration
* emitted through a dedicated service provider

This allows:

* compliance requirements in production
* minimal overhead in development

---

## Rate Limiting / Throttling

Where implemented, throttling is applied as a middleware.

Typical use-cases:

* login attempt throttling
* password reset request throttling
* API abuse protection

Some throttling middleware may not yet be migrated.

---

## Maintainability Impact (Thesis rationale)

Security remains maintainable in Lapis Orkestra because:

* security features are modular (module-driven)
* enforcement is registry-based (traceable wiring)
* payloads are typed (DTO contracts)
* infrastructure is swappable (utilities/providers)
* policies can be applied at the correct lifecycle stage

This supports incremental evolution:

* introduce new security middleware without refactoring actions
* upgrade JWT payload rules without changing controllers
* add response header policies without touching modules

---

## Known Gaps (Incremental Migration)

The following items are recognised as incomplete:

* full Verifier RBAC enforcement
* full security middleware set migration
* test coverage for security flows

These gaps are a consequence of time constraints and are planned for future iterations.

---

## Next Document

Proceed to [**CLI.md**](../docs/CLI.md) to read on:

* console lifecycle (`LapisConsole::run()`)
* command discovery
* reusing Actions outside HTTP
  
---

[← Back to DTO (Data Transfer Objects) README](../docs/DTO.md)
[← Back to Main README](../README.md)
