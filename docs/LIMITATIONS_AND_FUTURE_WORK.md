# Limitations and Future Work

---

> For full project context, return to [Main README](../README.md)
>
> [← Back to Testing](../docs/TESTING.md)

---

This document outlines the **known limitations** of Lapis Orkestra at its current stage and proposes **future work** directions.

The intent of this document is academic transparency. The framework is presented as a **research and architectural artefact**, not as a production-ready system.

---

## Scope Context

Lapis Orkestra was developed under the constraints of:

* limited research timeline
* concurrent academic obligations
* focus on architectural clarity over feature completeness

As such, certain features were intentionally deferred.

---

## Known Limitations

### 1. Incomplete Test Coverage

* No comprehensive PHPUnit test suite exists yet
* Unit and integration tests are largely unimplemented
* Validation relies primarily on static analysis tools

**Impact:**

* Higher reliance on manual verification
* Reduced confidence in edge-case behaviour

---

### 2. Partial Security Migration

* Some security middleware from the previous framework generation were not migrated
* RBAC enforcement via Verifiers is not fully implemented
* Throttling and rate limiting coverage is incomplete

**Impact:**

* Security posture is structurally sound but not fully enforced

---

### 3. Verifier Role Enforcement

* Verifier architecture exists as a facade
* Role resolution logic has not been fully wired

**Impact:**

* Access control rules are not fully active at runtime

---

### 4. Limited Production Hardening

* No formal performance benchmarking
* No production stress testing
* Limited error recovery and resilience testing

**Impact:**

* Framework suitability for high-load environments is unverified

---

### 5. Documentation Evolution

* Documentation reflects current architecture accurately
* Some areas may evolve as the framework matures

**Impact:**

* Minor inconsistencies may appear as features are added

---

## Design Trade-offs

Several trade-offs were made intentionally:

* prioritising architectural clarity over feature richness
* preferring explicitness over convenience
* choosing incremental extensibility over rapid prototyping

These trade-offs align with the thesis objective rather than immediate production use.

---

## Future Work

### 1. Comprehensive Test Suite

* Introduce PHPUnit configuration
* Add unit tests for Actions, Checkers, DTOs, and Verifiers
* Add integration tests for module discovery and registries

---

### 2. Full Security Enforcement

* Complete RBAC logic within Verifiers
* Migrate remaining security middleware
* Introduce consistent throttling policies

---

### 3. Performance and Scalability Testing

* Benchmark request lifecycle performance
* Evaluate registry overhead under load
* Measure adapter/provider resolution costs

---

### 4. Improved Developer Tooling

* CLI commands for debugging registries
* Registry snapshot and inspection utilities
* Better error diagnostics and reporting

---

### 5. Documentation Enhancements

* Add architectural decision records (ADR)
* Expand examples for custom modules and providers
* Provide migration guides for future versions

---

### 6. Production Readiness Path

* Introduce environment-specific hardening profiles
* Add optional strict runtime checks
* Improve logging and monitoring integration

---

## Academic Reflection

The limitations identified in this document reflect real-world constraints commonly encountered in software engineering projects.

Rather than weakening the research contribution, these limitations:

* highlight the importance of incremental development
* demonstrate the value of designing for evolution
* reinforce the thesis focus on maintainability

---

## Summary

* Lapis Orkestra is architecturally complete but feature-incomplete
* Limitations are documented transparently
* Future work is clearly defined and achievable

This reinforces the framework’s role as a **maintenance-focused research artefact** rather than a finished product.

---

[← Back to Testing](../docs/TESTING.md)

[← Back to Main README](../README.md)
