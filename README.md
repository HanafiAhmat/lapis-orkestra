<p align="center">
  <img src="docs/assets/lapis-orkestra-logo.png" alt="Lapis Orkestra Logo" width="180">
</p>
<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.1+-777bb4?style=flat-square" alt="PHP">
  <img src="https://img.shields.io/badge/Architecture-Modular%20MVC-blueviolet?style=flat-square" alt="Architecture">
  <img src="https://img.shields.io/badge/Security-OWASP%20Compliant-4caf50?style=flat-square" alt="Security">
</p>

# Lapis Orkestra

**A Modular PHP Framework for Maintenance & Incremental Development**

> **Master’s Thesis Context**
> **Topic:** *Maintenance and Incremental Development of Software, with a Specific Focus on Modularity*

Lapis Orkestra is a research‑driven PHP framework designed to study, demonstrate, and evaluate how **long‑lived backend systems** can be maintained and evolved incrementally through **clear separation of responsibilities, modular boundaries, and explicit interaction contracts**.

This project is intentionally architectural and educational in nature. It prioritises **clarity, traceability, and extensibility** over rapid feature delivery or production readiness.

---

## Project Status

⚠️ **Important Notice (Thesis Build)**

* ✅ Core runtime, routing, middleware, and module system are operational
* ✅ Framework can be consumed as a Composer dependency by a child application
* ✅ External vendor modules can be installed and integrated
* ✅ Code quality enforced using **PHPStan** and **PHPCS / Easy Coding Standard**
* ⏳ Unit and integration testing are **not yet implemented**
* ⏳ Some components from earlier framework generations are intentionally not migrated

This is **not a production‑ready framework**. The primary goal is to support academic discussion on software maintenance and modular system design.

---

## Architectural Philosophy

Lapis Orkestra is **not a monolithic framework**, although it fully supports a **monolithic deployment model**.

By design, the framework can be consumed by:

* a traditional frontend web application
* a mobile application
* API clients such as Postman

Access is provided via:

* RESTful APIs
* default web view templates (for direct browser access)

This dual capability allows the same core system to serve **multiple client types** without architectural changes.

---

## Core Design Goals

1. **Maintainability over time**
2. **Incremental development without refactoring the core**
3. **Explicit separation of responsibilities**
4. **Replaceable and optional modules**
5. **Traceable global state and system behaviour**

The framework avoids implicit magic and instead encourages **explicit structure and intention‑revealing components**.

---

## High‑Level System Composition

Lapis Orkestra is composed of three distinct layers:

1. **Framework Core**
   Provides kernel bootstrapping, routing, middleware handling, registries, and shared infrastructure.

2. **Child Application (Skeleton Project)**
   Depends on the framework via Composer and defines application‑specific configuration and modules.

3. **Vendor Modules**
   Self‑contained, installable feature packages distributed via Composer.

This structure allows the framework to evolve independently from applications built on top of it.

---

## Internal Structure Overview

The framework is organised by **responsibility**, not by technical convenience. Each directory represents a clearly defined role within the system.

### Framework‑Level Responsibilities

* **Kernel** – Application lifecycle and boot sequence
* **Loaders** – Controlled loading of framework components
* **Routes** – Route definitions and registration
* **Middlewares** – Pre‑business request processing
* **ResponseFilters** – Post‑business response processing
* **Registries** – Centralised containers for shared system state
* **Utilities** – Internal system adapters (filesystem, environment, runtime)
* **Services** – External system providers (email, APIs, third‑party services)
* **Controllers** – HTTP request entry points
* **Views** – Default web view templates

---

## Responsibility‑Driven Components

### Checkers

**Purpose:** Input validation only

* Validate request payloads and parameters
* Contain no business logic
* Ensure data correctness before entering the business layer

This strict separation prevents validation logic from leaking into controllers or actions.

---

### Actions

**Purpose:** Business logic execution

* Each controller action maps to a dedicated Action class
* Encapsulate use‑case‑specific business rules
* Coordinate domain operations without handling transport concerns

Actions represent the **core behaviour** of the system.

---

### Verifiers

**Purpose:** Access and permission verification

* Validate whether a user or actor is allowed to perform an operation
* Separate authorisation concerns from business logic
* Often used before or within Actions

This ensures consistent and auditable access control.

---

### Data Transfer Objects (DTO)

**Purpose:** Structured information transfer

* Convert loose arrays into strongly‑typed objects
* Enforce explicit data contracts
* Document the meaning and type of each attribute

DTOs prevent ambiguous or undocumented data from propagating through the system.

---

### Response Filters

**Purpose:** Post‑business response modification

* Executed **after** controller actions
* Modify, filter, or enrich responses
* Similar in concept to middleware, but intentionally positioned after business processing

This separation allows business logic to remain response‑agnostic.

---

## Registries (Controlled Global State)

Lapis Orkestra replaces ad‑hoc global variables, constants, and scattered session usage with **explicit registries**.

Each registry has a **single, clearly defined role**.

Examples include:

* **VarRegistry** – Centralised global variable storage
* **RouteRegistry** – Registered routes
* **MiddlewareRegistry** – Available middlewares
* **InteractorRegistry** – Module interaction endpoints
* **ResponseFilterRegistry** – Response filter management
* **ConfigRegistry** – Resolved configuration values

Registries act as **traceable containers**, making global state observable and maintainable.

---

## Modules

Modules are the primary unit of extensibility.

A module may provide:

* controllers
* actions
* checkers
* verifiers
* interactors
* migrations and seeds
* registries
* routes
* views

Modules can exist in:

1. **Child Application** – application‑specific logic
2. **Vendor Packages** – reusable features distributed via Composer

A sample vendor module is included to demonstrate real‑world installation and integration.

---

## Interactors (Module Communication)

Interactors define how modules communicate with each other.

* Act as public APIs exposed by a module
* Prevent direct internal coupling between modules
* Enable controlled, versionable cross‑module interaction

This mechanism supports incremental feature growth without architectural erosion.

---

## Utilities vs Services

### Utilities (Adapters)

* Interact with **internal system or server components**
* Examples: filesystem, environment, runtime state
* Implemented using pluggable adapters

Utilities do **not** communicate with external systems.

---

### Services (Providers)

* Interact with **external systems**
* Examples: email gateways, payment APIs, third‑party platforms
* Implemented using pluggable providers

This distinction isolates infrastructure volatility from core logic.

---

## Incremental Development Model

Lapis Orkestra supports gradual system evolution:

1. Start with the kernel and minimal routing
2. Introduce modules as requirements emerge
3. Add services only when external integration is required
4. Extend functionality via new modules instead of modifying existing ones
5. Replace or remove modules without rewriting unrelated code

This reflects real‑world maintenance scenarios rather than greenfield assumptions.

---

## Code Quality

The current focus is on **static correctness and readability**:

* PHPStan for type safety and structural validation
* PHPCS / Easy Coding Standard for consistency

Automated testing is planned but outside the scope of the current thesis phase.

---

## Intended Audience

* Academic evaluators and supervisors
* Software engineers studying maintainability
* Developers interested in modular backend architecture
* Future maintainers of long‑lived PHP systems

---

## Non‑Goals

Lapis Orkestra does not aim to:

* Compete with mainstream PHP frameworks
* Provide rapid scaffolding or convenience abstractions
* Hide complexity behind implicit behaviour
* Claim production readiness

Clarity and explicit design are prioritised over speed.

---

## Research Context

This framework is developed as part of a Master’s programme in Computer Science, focusing on **software maintenance and incremental development through modular design**.
This repository supports Chapters 4 and 5 of the accompanying Master’s thesis.

Supporting documentation expands on architectural decisions and their academic justification.

> Continue reading:
>
> 🔗 [Architecture](docs/ARCHITECTURE.md)
>
> 🔗 [Modules](docs/MODULES.md)
>
> 🔗 [Configuration](docs/CONFIG.md)
>
> 🔗 [Registries](docs/REGISTRIES.md)
>
> 🔗 [Utilities](docs/UTILITIES.md)
>
> 🔗 [Services](docs/SERVICES.md)
>
> 🔗 [Request Lifecycle](docs/REQUEST_LIFECYCLE.md)
>
> 🔗 [Controller Execution Model](docs/CONTROLLER_EXECUTION_MODEL.md)
>
> 🔗 [DTO (Data Transfer Objects)](docs/DTO.md)
>
> 🔗 [Security](docs/SECURITY.md)
>
> 🔗 [CLI](docs/CLI.md)
>
> 🔗 [Testing](docs/TESTING.md)
>
> 🔗 [Limitations and Future Work](docs/LIMITATIONS_AND_FUTURE_WORK.md)

---

## Related Repositories

Lapis Orkestra is designed to be used as a **core framework** with external applications and vendor modules. The following repositories demonstrate real, working usage of the framework:

### 🧩 Application Skeleton

**Lapis Orkestra App Skeleton** provides a ready-to-run child application that depends on the framework via Composer.

* Demonstrates how to:

  * bootstrap a project using Lapis Orkestra
  * configure modules, services, and utilities
  * run the application in a browser
* Acts as a reference implementation for real-world usage

🔗 [https://github.com/HanafiAhmat/lapis-orkestra-app-skeleton](https://github.com/HanafiAhmat/lapis-orkestra-app-skeleton)

---

### 📦 Vendor Module Example

**Lapis Orkestra Vendor Blog Module** is a sample third-party module installable via Composer.

* Demonstrates:

  * modular feature packaging
  * module discovery and registration
  * routes, controllers, actions, and UI contributions from a vendor module
* Serves as a reference for developers building reusable Lapis Orkestra modules

🔗 [https://github.com/HanafiAhmat/lapis-orkestra-vendor-blog-module](https://github.com/HanafiAhmat/lapis-orkestra-vendor-blog-module)

---

## Ecosystem Overview

Together, these repositories form a complete working ecosystem:

* **Lapis Orkestra Framework** — core runtime, architecture, and lifecycle
* **App Skeleton** — real application integration and configuration
* **Vendor Blog Module** — reusable, Composer-installed feature module

This ecosystem validates the framework’s design goals of **modularity**, **maintainability**, and **incremental development**.

