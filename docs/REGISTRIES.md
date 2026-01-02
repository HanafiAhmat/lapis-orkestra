# Registries

---

> For full project context, return to [Main README](../README.md)
> 
> [← Back to Configuration](../docs/CONFIG.md)

---

Registries are a foundational architectural element in Lapis Orkestra.

They exist to solve a common maintainability problem in long-lived systems:

> **Global state and global availability are often necessary, but uncontrolled globals become untraceable and unmaintainable.**

Instead of allowing state and system-wide definitions to be scattered across:

* PHP constants
* global variables
* static properties
* ad-hoc session usage
* hidden framework containers

Lapis Orkestra uses **explicit registries**:

* Each registry has a **single responsibility**
* Data stored in registries becomes **traceable and inspectable**
* System-wide access becomes consistent and intentional

---

## What is a Registry?

A registry is a dedicated container that provides:

* `set(key, value)`
* `get(key)`
* `has(key)`
* (optionally) structured validation for stored values

Registries are **not** a generic dependency container. They are **role-specific stores**.

---

## Why Multiple Registries Instead of One Container?

A single “global container” usually becomes:

* a dumping ground
* a hidden coupling mechanism
* difficult to audit

Lapis Orkestra uses **multiple registries** so that:

* state is grouped by meaning
* responsibilities remain clear
* access patterns are predictable
* debugging is simpler

In a maintainable architecture, it should be clear whether a value is:

* configuration
* a runtime variable
* a registered route
* a middleware mapping
* a response filter
* an interactor endpoint

Registries encode those distinctions.

---

## Registry Discovery (Framework)

Framework-level registries are located in:

```
src/Framework/Registries/
```

Examples from this codebase:

* `AdminMenuRegistry.php`
* `AdminWidgetRegistry.php`
* `ConfigRegistry.php`
* `InteractorRegistry.php`
* `MiddlewareRegistry.php`
* `PublicMenuRegistry.php`
* `PublicWidgetRegistry.php`
* `ResponseFilterRegistry.php`
* `RouteRegistry.php`
* `VarRegistry.php`

> Modules may also provide module-level registries under `src/Modules/<Module>/Registries/`.

---

## Core Registries and Their Responsibilities

Below is the role and purpose of the primary registries.

### 1) ConfigRegistry

**Responsibility:** resolved configuration values

* Central source of truth for configuration
* Supports layered overrides (framework → modules → app → env)
* Prevents repeated `getenv()` or scattered configuration arrays

Used by:

* boot process
* utilities/services adapter/provider selection
* routes/middleware conditional behaviours

---

### 2) VarRegistry

**Responsibility:** traceable global runtime variables

VarRegistry exists to replace:

* global variables
* constants used as globals
* session variables abused as global storage

It provides:

* a single observable location for shared runtime state
* predictable read/write access
* a maintainable alternative to hidden cross-cutting state

**Example use-cases:**

* request identifiers
* runtime mode flags
* boot state markers
* shared context values used across layers

> Thesis rationale: This supports maintainability by making global state auditable.

---

### 3) RouteRegistry

**Responsibility:** registered route definitions

Routes are not stored as loose arrays; they are stored as structured definitions.

Stored values typically include:

* HTTP method
* URI path
* controller/handler
* middleware keys
* response filter keys

This enables:

* route discovery
* debugging and introspection
* consistent route compilation for different router adapters

---

### 4) MiddlewareRegistry

**Responsibility:** middleware key → class mapping

Instead of directly referencing middleware classes everywhere, Lapis uses keys:

* improves readability
* supports swapping middleware implementations
* allows modules to register their own middleware keys

Example:

```php
Lapis::middlewareRegistry()->set(
  'core.user.user_type_availability',
  UserTypeAvailabilityMiddleware::class
);
```

Certain routes may use the same middleware however, some of them may require slightly different condition. MiddlewareRegistry allow for such cases. For example, editing some of other customer information may be allowed by a staff but it is forbidden totally for a customer to do so. Another case would be both staff and customer may edit a blog post.

Example of AuthMiddleware registration:

```php
Lapis::middlewareRegistry()->set(
	'core.security.auth', 
	AuthMiddleware::class
);
```

Example usage of AuthMiddleware to allow only staff user during route registration:

```php
$route->add(
	'PUT', 
	'/customers/{id:\d+}', 
	[CustomerController::class, 'update'],
	[new MiddlewareDefinition('core.security.auth', [['staff']])]
);
```

Example usage of AuthMiddleware to allow both staff and customer user during route registration:

```php
$route->add(
	'PUT', 
	'/blog/post/{id:\d+}', 
	[BlogPostController::class, 'update'],
	[new MiddlewareDefinition('core.security.auth', [['staff', 'customer']])]
);
```

During route matching in the kernel dispatcher, AuthMiddleware will be instantiated with additional variables defined during route registration and added into the middleware relay queue.

```php
// Build middleware queue
$queue = [];

// Framework's Required Middleware
$queue[] = new SessionMiddleware();
...

// fetch pre action middleware
foreach ($routeMatch->middlewares as $mwDef) {
    if (Lapis::middlewareRegistry()->has($mwDef->id)) {
        $mwFactory = Lapis::middlewareRegistry()->get($mwDef->id);
        $queue[] = $mwFactory instanceof Closure ? $mwFactory(...$mwDef->vars) : new $mwFactory(
            ...$mwDef->vars
        );
    }
}

$queue[] = new FinalHandlerMiddleware($routeMatch);

// Relay through middleware
$relay = new Relay($queue);
```

---

### 5) ResponseFilterRegistry

**Responsibility:** response filter key → class mapping

Response filters apply **after** the main business action completes.

They exist to:

* shape responses consistently
* add headers or metadata
* apply formatting or transformation rules

This avoids contaminating business logic with response post-processing.

---

### 6) InteractorRegistry

**Responsibility:** interactor key → class mapping

Interactors are module-to-module APIs.

InteractorRegistry ensures:

* discoverability of module APIs
* stable keys for cross-module dependency
* enforcement of `InteractorInterface`

Example registration:

```php
Lapis::interactorRegistry()->set(
  'vendor.ampas.blog',
  BlogInteractor::class
);
```

Example usage:

```php
if ($interactor = Lapis::interactorRegistry()->getOrSkip('vendor.ampas.blog')) {
    $interactor::doSomeThing();
}
```

---

### 7) AdminMenuRegistry / PublicMenuRegistry

**Responsibility:** menu definitions for view rendering

* Allows modules (core/vendor/app) to add menu items
* Keeps UI contribution modular
* Separates view composition from controller logic

---

### 8) AdminWidgetRegistry / PublicWidgetRegistry

**Responsibility:** UI widgets for view rendering

Widgets allow modules to contribute UI components to shared layouts.

---

## How Registries Support Incremental Development

Registries support incremental growth because:

* new features register themselves instead of patching existing code
* modules can be added/removed while keeping system wiring stable
* the system remains discoverable (what exists is in registries)

A typical incremental feature addition might:

1. Add a new module
2. The module registers:

   * middleware keys
   * routes
   * interactors
   * UI menus/widgets
3. The system automatically sees these additions during boot

No existing module needs to be edited.

---

## How Registries Prevent Architectural Erosion

Without registries, systems often evolve into:

* copy/pasted wiring
* hidden global constants
* duplicated middleware references
* undocumented cross-module imports

Registries prevent this by enforcing:

* a small number of official “places” where system-wide definitions live
* consistent access patterns
* explicit roles per container

---

## Guardrails and Constraints

Registries are powerful, but must remain disciplined.

### Recommended rules

* Registries should store **definitions**, not business data
* Avoid storing large dynamic datasets
* Keep keys namespaced (e.g. `core.user.*`, `vendor.ampas.blog.*`)
* Registries should be initialised during boot, not randomly at runtime

### Candidate improvement (future)

* Add strict schema validation per registry
* Add debugging dump utilities (registry snapshots)
* Add immutable mode after boot (to prevent runtime mutation)

---

## Next Document

Proceed to [Utilities](../docs/UTILITIES.md) to read on:

* what Utilities do
* Adapter pattern and discovery
* server/internal vs external separation

---

[← Back to Configuration](../docs/CONFIG.md)

[← Back to Main README](../README.md)
