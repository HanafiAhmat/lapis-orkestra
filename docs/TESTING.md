# Testing

---

> For full project context, return to [Main README](../README.md)
> [← Back to CLI README](../docs/CLI.md)

---

This document describes the **testing strategy and current testing status** of Lapis Orkestra.

Testing is discussed transparently and realistically, in line with the thesis focus on **maintenance and incremental development** rather than premature completeness.

---

## Current Testing Status

⚠️ **Important Notice**

At the time of writing:

* Automated unit and integration tests are **not fully implemented**
* No complete PHPUnit test suite exists yet
* Code quality and correctness were prioritised through:

  * static analysis (PHPStan)
  * coding standards enforcement (PHPCS / ECS)

This is an intentional and documented decision driven by **time constraints** and **research scope**, not by architectural limitations.

---

## Why Tests Were Deferred

The primary goals of this project were:

1. Design a modular architecture
2. Enable incremental development and migration
3. Establish clean separation of concerns
4. Ensure code readability and traceability

Given limited time, effort was focused on:

* architecture clarity
* maintainable layering
* explicit contracts (DTOs, interfaces)
* registry-driven wiring

These foundations are prerequisites for effective testing.

---

## Architecture Designed for Testability

Although tests are not yet implemented, the architecture explicitly supports testing.

### Key test-friendly design choices

* **Actions** contain business logic and are reusable
* **Verifiers** isolate access logic (RBAC seam)
* **Checkers** perform deterministic validation
* **DTOs** enforce structured data contracts
* **Utilities** hide infrastructure behind adapters
* **Services** isolate external systems behind providers
* **Registries** centralise wiring and global state

These choices minimise hidden dependencies and enable mocking or substitution during tests.

---

## Unit Testing Targets

The following components are designed to be unit-testable in isolation:

### Actions

* primary candidates for unit tests
* business logic without HTTP dependency
* entity interaction can be mocked or sandboxed

### Checkers

* deterministic input validation
* simple boolean outcomes
* ideal for fast unit tests

### Verifiers

* RBAC logic isolated from controllers
* role resolution can be mocked

### DTOs

* `fromArray()` and factory-based creation
* validation rules are deterministic
* failure cases are testable

---

## Integration Testing Targets

Integration tests are suitable for:

* module boot and discovery
* registry population
* adapter/provider selection via configuration
* request lifecycle wiring

The registry-driven design allows inspection of system state without invoking full HTTP stacks.

---

## Testing Utilities and Services

### Utilities

Utilities can be tested by:

* swapping adapters (e.g. file cache vs APCU)
* injecting test adapters via configuration

### Services

Services support:

* mock providers
* test-specific providers
* environment-based provider selection

This enables safe testing of business logic without contacting real external systems.

---

## CLI Testing

CLI commands are testable because:

* commands are thin
* business logic lives in Actions
* Symfony Console provides testing helpers

Commands can be tested independently from HTTP runtime.

---

## Why Static Analysis Was Prioritised

Static analysis was chosen as an early quality gate because it:

* detects type errors early
* enforces architectural boundaries
* improves code readability
* reduces runtime uncertainty

Tools used:

* **PHPStan** — static type analysis
* **PHPCS / ECS** — coding standards enforcement

This aligns with the thesis goal of **maintainable evolution**.

---

## Planned Testing Strategy (Future Work)

The intended testing roadmap includes:

1. Introduce PHPUnit configuration
2. Add unit tests for:

   * DTOs
   * Checkers
   * Actions
3. Add verifier/RBAC tests
4. Add registry population tests
5. Add limited end-to-end tests for request lifecycle

Testing will be added incrementally without refactoring architecture.

---

## Academic Justification

From a research perspective:

* architecture precedes test completeness
* testability is a design property, not a test count
* incremental delivery mirrors real-world constraints

This project demonstrates how **designing for testability** enables future testing without architectural debt.

---

## Summary

* Tests are not fully implemented yet
* Architecture explicitly supports testing
* Static analysis ensured early quality
* Testing is planned as an incremental phase

This transparent approach reflects real-world software maintenance and evolution.

---

## Next Document

Proceed to [**LIMITATIONS_AND_FUTURE_WORK.md**](../docs/LIMITATIONS_AND_FUTURE_WORK.md).

---

[← Back to CLI README](../docs/CLI.md)
[← Back to Main README](../README.md)
