# Architecture

---

> For full project context, return to [Main README](../README.md)

---

This document explains **how Lapis Orkestra is structured** and **how it executes** at runtime.

It is written for the thesis topic:

> *Maintenance and Incremental Development of Software, with a Specific Focus on Modularity*

Lapis Orkestra’s architecture emphasises:

* **Responsibility-driven directories** (clear roles)
* **Module-based growth** (incremental development)
* **Controlled interaction points** (Interactors, Registries)
* **Replaceable infrastructure** (Utilities via Adapters, Services via Providers)

---

## System Composition

Lapis Orkestra is typically used as a Composer dependency by a child project.

### 1) Framework Repository

Provides:

* kernel and bootstrapping
* routing and middleware pipeline
* registries and shared infrastructure
* module system (core + vendor + app modules)

### 2) Child Application (Skeleton)

Provides:

* application configuration overrides
* application routes and controllers
* optional app-local modules

### 3) Vendor Modules (Composer packages)

Provides:

* installable feature modules
* integrated without modifying core

---

## Directory Structure (Framework)

The framework is organised by responsibility.

### `src/Framework/*` — Framework foundation

Key responsibility folders:

* **Kernel** — boot + dispatch coordination
* **Loaders** — discovery & registration (modules, commands, etc.)
* **Routes** — framework-provided route sets
* **Middlewares** — pre-business request processing
* **ResponseFilters** — post-business response processing
* **Registries** — system-wide containers (global but traceable)
* **Controllers** — framework controllers (where applicable)
* **Views** — default templates

### `src/Modules/*` — Core modules bundled with the framework

Example modules in this codebase:

* `Modules/User`
* `Modules/Security`
* `Modules/SystemMonitor`

Each module can contain:

* `Actions`, `Checkers`, `Controllers`, `Interactors`, `Registries`, etc.

### `src/Utilities/*` — internal / server interactions (Adapters)

Utilities interact with **internal components** (filesystem, runtime, routing engines, request parsing).

They are implemented using **pluggable Adapters**.

Example in this codebase:

* `Utilities/Adapters/Router/AuraRouterAdapter.php`
* `Utilities/Adapters/Router/FastRouteRouterAdapter.php`

### `src/Services/*` — external interactions (Providers)

Services interact with **external systems** (email gateways, third-party APIs).

Providers are designed to be **swappable**.

Example in this codebase:

* `Services/Providers/Mailer/MailFunctionMailerProvider.php`
* `Services/Providers/Mailer/SmtpMailerProvider.php`

---

## Entry Points

Lapis has two explicit entry points:

### Web Entry Point

* `public/index.php` calls:

```php
\BitSynama\Lapis\Lapis::run();
```

The **web runtime entry point** is:

* `Lapis::run()`

### Console Entry Point

* `bin/console` calls:

```php
\BitSynama\Lapis\LapisConsole::run();
```

The **console runtime entry point** is:

* `LapisConsole::run()`

---

## Web Runtime Lifecycle (Lapis::run)

At a high-level, web runtime follows this flow:

```
public/index.php
  -> Lapis::run()
      -> Lapis::boot()
      -> Dispatcher::dispatch()
      -> Emitter::emit(Response)
```

### 1) Boot Sequence (Lapis::boot)

`Lapis::boot()` is idempotent (boot once).

Core boot responsibilities include:

1. **Resolve runtime directories**

   * repo dir
   * project dir (child app)
   * tmp dir

2. **Setup core infrastructure**

   * Emitter (HTTP vs Console)
   * VarRegistry
   * ConfigRegistry
   * LoggerUtility
   * FailsafeErrorHandler
   * MultiResponse handler

3. **Module system initialisation**

   * `setupModules()`
   * includes registries needed for module orchestration:

     * `UserTypeRegistry`
     * `InteractorRegistry`
     * `MiddlewareRegistry`
     * `ResponseFilterRegistry`

4. **Utilities initialisation**

   * CacheUtility
   * CookieUtility
   * RequestUtility
   * HttpClientUtility
   * SessionUtility
   * RouterUtility
   * ViewUtility

5. **Database connection + readiness check**

   * `setupDbConnection()`
   * if not CLI and DB not ready, Lapis generates a **service unavailable** response

6. **Register UIs and Routes (Web only)**

   * `setupUIs()`
   * `setupRoutes()`

> This boot sequence supports incremental development: infrastructure is loaded consistently, while feature growth is driven through modules.

---

### 2) Request Dispatch (Framework\Kernel\Dispatcher)

After `boot()`, Lapis executes:

```php
$dispatcher = new Dispatcher();
$response = $dispatcher->dispatch();
```

The dispatcher:

1. **Builds a PSR-7 ServerRequest**
2. **Applies method override**

   * `X-HTTP-Method-Override`
   * `_method` in parsed body
3. **Matches the route** using a router adapter
4. **Builds the middleware queue**
5. **Relays through middleware pipeline**
6. **Relays through response filters (post-business)**

#### Middleware Pipeline (Pre-business)

The dispatcher constructs a queue including framework-required middleware such as:

* SessionMiddleware
* ClientIp middleware
* JsonPayload middleware
* UrlEncodePayload middleware

It also conditionally adds middlewares registered in `MiddlewareRegistry`.

#### Response Filters (Post-business)

After the middleware pipeline completes, the dispatcher applies response filters defined by the matched route that will be retrieved from `ResponseFilterRegistry`.

This is an intentional distinction:

* **Middleware** happens before business logic (request-side)
* **ResponseFilters** happen after business logic (response-side)

This supports maintainability by keeping business logic response-agnostic while still allowing system-wide response shaping.

---

## Routing Architecture

### Route definitions are stored in a registry

Routes are registered into **RouteRegistry** as `RouteDefinition` objects.

### Router is a pluggable Utility (Adapter-based)

`RouterUtility` selects a router adapter based on configuration and discovery.

Example adapters:

* Aura router adapter (`AuraRouterAdapter`)
* FastRoute router adapter (`FastRouteRouterAdapter`)

Adapters implement `RouterAdapterInterface` and are discoverable through adapter metadata.

### Route registration order (Lapis::setupRoutes)

The route registration order is intentionally layered:

1. Module routes (if module loader exists)
2. Framework Admin routes
3. Framework Public routes
4. Child app routes (`AppRoutes`) when present

This structure supports incremental growth and overrides:

* modules can introduce routes without touching core
* child apps can extend the framework without forking it

---

## Module Architecture

Modules exist in three sources and are unified by `ModuleLoader` into `ModuleDefinition` objects.

### 1) Core Modules (Framework bundled)

Discovered from framework `src/Modules/*` and `src/config/modules.php`.

Composite key format:

* `core.<ModuleKey>` (e.g. `core.User`)

### 2) Vendor Modules (Composer packages)

Discovered using Composer’s `InstalledVersions` by type:

* `bitsynama-lapis-module`

Vendor modules declare module metadata in `composer.json` under:

* `extra.lapis-module`

Composite key format:

* `vendor.<ModuleKey>`

### 3) App Modules (Child project)

Discovered in the child project (application-level modules).

Composite key format:

* `app.<ModuleKey>`

### Load order

Modules are sorted by `priority` and stored into:

* `ConfigRegistry['modules']`

### Module Responsibilities

A module may register:

* handlers
* routes
* UIs

Module registration is executed through the module’s `*Module.php` class.

---

## Controller → Checker → Action (Responsibility Pipeline)

Lapis’s internal responsibilities are intentionally separated:

* **Controllers**: transport entry points (HTTP)
* **Checkers**: input validation only
* **Actions**: business logic for a single controller action
* **Verifiers**: access/permission verification
* **DTOs**: structured data transfer instead of loose arrays

This separation is a key architectural mechanism used to prevent maintenance erosion.

---

## Console Runtime Lifecycle (LapisConsole::run)

Console runtime shares the same boot process:

```
bin/console
  -> LapisConsole::run()
      -> Lapis::boot()
      -> Symfony Console Application
      -> Command discovery + registration
      -> $console->run()
```

### Command discovery

Commands are discovered by scanning `Commands` directories across:

* framework
* project
* modules

This is implemented via:

* `Framework\Loaders\CommandAutoLoader`
* directory discovery using `Framework\Foundation\Atlas`

---

## Why this architecture supports maintainability

Lapis Orkestra was designed to keep system evolution controlled:

* Clear boundaries reduce accidental coupling
* Modules allow new features without altering core
* Interactors provide contract-based cross-module communication
* Registries centralise shared state in traceable containers
* Adapters/Providers isolate technical volatility
* ResponseFilters allow response shaping without polluting business logic

---

## Next Documents

After this architecture overview, the recommended next docs are:

* [MODULES](../docs/MODULES.md) — module anatomy, app vs vendor, discovery rules, Interactors
* [REGISTRIES](../docs/REGISTRIES.md) — registry roles, VarRegistry rationale, traceability benefits
* [UTILITIES](../docs/UTILITIES.md) / [SERVICES](../docs/SERVICES.md) — adapter vs provider boundaries

---

[← Back to Main README](../README.md)
