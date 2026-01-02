# Modules

---

> For full project context, return to [Main README](../README.md)
> [← Back to Architecture README](../docs/ARCHITECTURE.md)

---

This document describes the **module system** in Lapis Orkestra: how modules are discovered, loaded, prioritised, and how they register capabilities (handlers, routes, and UIs).

In the thesis context, modules are the primary mechanism enabling:

* **incremental development** (add features without rewriting core)
* **maintenance isolation** (limit blast-radius of changes)
* **replaceability** (swap implementations via Composer packages)

---

## What is a Module in Lapis Orkestra?

A **module** is a self-contained feature unit that can contribute to the system by registering:

1. **Handlers** (system registrations)
2. **Routes** (HTTP endpoints)
3. **UIs** (menus/widgets used by the view layer)

A module is represented by a *Module class* named:

```
<ModuleKey>Module
```

Examples from this codebase:

* `BitSynama\Lapis\Modules\User\UserModule`
* `Ampas\Blog\BlogModule`

The module class implements:

```php
BitSynama\Lapis\Framework\Contracts\ModuleInterface
```

and provides:

```php
public static function registerHandlers(): void;
public static function registerRoutes(): void;
public static function registerUIs(): void;
```

> **Note (current implementation detail):**
> `ModuleLoader` instantiates the module class and calls these methods through the instance. The methods are declared `static` in the interface but invoked via an instance. This works in PHP, but it is a candidate for cleanup (either make them non-static, or call them statically).

---

## Module Sources

Lapis discovers modules from three sources:

### 1) Core Modules (bundled with the framework)

* Location: `src/Modules/*`
* List file: `src/config/modules.php`
* Composite key format: `core.<ModuleKey>`

Example:

* `core.User`
* `core.Security`

### 2) Vendor Modules (installed via Composer)

* Composer package type: `bitsynama-lapis-module`
* Declared metadata: `composer.json > extra > lapis-module`
* Composite key format: `vendor.<ModuleKey>`

Example:

* `vendor.Blog`

### 3) App Modules (defined inside the child project)

* Location: `app/Modules/*`
* List file: `app/config/modules.php`
* Composite key format: `app.<ModuleKey>`

Example:

* `app.Catalog`

---

## Module Discovery and Load Order

Module discovery is implemented in:

* `BitSynama\Lapis\Framework\Loaders\ModuleLoader`

### Discovery sequence

The loader discovers modules in this order:

1. `discoverCoreModules()`
2. `discoverVendorModules()`
3. `discoverAppModules()`

After discovery, modules are sorted by `priority` (ascending):

```php
uasort($this->modules, fn ($a, $b) => $a->priority <=> $b->priority);
```

and stored for system-wide access under:

```php
Lapis::configRegistry()->set('modules', $this->modules);
```

### Composite key

All modules are stored by a composite key:

```
<source>.<moduleKey>
```

Examples:

* `core.User`
* `vendor.Blog`
* `app.Catalog`

---

## ModuleDefinition (Internal DTO)

Each discovered module becomes a `ModuleDefinition`:

* `BitSynama\Lapis\Framework\DTO\ModuleDefinition`

Key fields:

* `source` — `core`, `vendor`, or `app`
* `moduleKey` — module short name (e.g. `User`, `Blog`)
* `enabled` — whether module is active
* `priority` — controls load order
* `package` — composer package name (vendor only)
* `path` — absolute module path
* `namespace` — namespace prefix for module classes

---

## Module Registration Lifecycle

When Lapis boots, it initialises `ModuleLoader` and then calls module registration steps (via `Lapis::setupModules()` in the boot sequence).

A module contributes capabilities through three explicit hooks:

### 1) registerHandlers()

Used to register system-level entries such as:

* Middleware keys
* Interactor endpoints
* User types
* Registries entries

Example (core User module):

```php
public static function registerHandlers(): void
{
    Lapis::userTypeRegistry()->set('staff', StaffUserType::class);
    Lapis::userTypeRegistry()->set('customer', CustomerUserType::class);

    Lapis::middlewareRegistry()->set(
        'core.user.user_type_availability',
        UserTypeAvailabilityMiddleware::class
    );
}
```

### 2) registerRoutes()

Routes are registered into the framework router layer through the module’s routes class.

Example (core User module):

```php
public static function registerRoutes(): void
{
    UserRoutes::register();
}
```

```php
class UserRoutes implements ModuleRoutesInterface
{
    public static function register(): void
    {
        /** @var string $adminPrefix */
        $adminPrefix = Lapis::configRegistry()->get('app.routes.admin_prefix');
        $route = Lapis::routeRegistry();

        $route->addGroup($adminPrefix, function (RouteRegistry $route) {
            $route->addGroup('/staffs', function (RouteRegistry $route) {
                $route->add('GET', '', (new AdminStaffController())->list(...));
                $route->add('GET', '/{id:\d+}', (new AdminStaffController())->show(...));
                $route->add('GET', '/create', (new AdminStaffController())->create(...));
                $route->add('POST', '', (new AdminStaffController())->store(...));
                $route->add('GET', '/{id:\d+}/edit', (new AdminStaffController())->edit(...));
                $route->add('PUT', '/{id:\d+}', (new AdminStaffController())->update(...));
                $route->add('DELETE', '/{id:\d+}', (new AdminStaffController())->destroy(...));
            }, [
                new MiddlewareDefinition('core.security.auth'),
            ]);

            ...
       });
    }
}
```

### 3) registerUIs()

Used to register view-layer menus/widgets through UI registries.

Example (vendor Blog module registering menus):

```php
Lapis::adminMenuRegistry()->set('main', MenuItemDefinition::fromArray([...]))
Lapis::publicMenuRegistry()->set('main', MenuItemDefinition::fromArray([...]))
```

---

## Module Anatomy (Recommended Structure)

A module may include many internal components. Lapis encourages **responsibility separation** rather than dumping everything into controllers.

A typical module can contain:

* **Controllers** — HTTP entry points
* **Actions** — business logic for each controller action
* **Checkers** — input validation only
* **Verifiers** — access/permission verification
* **DTO** / **DTOs** — structured data transfer
* **Interactors** — module-to-module API surface
* **Middlewares** — module-specific middleware
* **Registries** — module-specific registry entries
* **Entities** — persistence models
* **Migrations / Seeds** — database lifecycle
* **Enums** — strict domain constants
* **Commands** — CLI commands
* **Views** — web templates

### Example: Core `User` module

Directory:

```
src/Modules/User/
  Actions/
  Checkers/
  Controllers/
  Entities/
  Enums/
  Interactors/
  Middlewares/
  Migrations/
  Registries/
  Seeds/
  UserTypes/
  Verifiers/
  Views/
  UserModule.php
  UserRoutes.php
  UserUIs.php
```

---

## Interactors (Module-to-Module Communication)

Interactors are the **public API surface** a module exposes to other modules.

The registry is:

* `BitSynama\Lapis\Framework\Registries\InteractorRegistry`

It enforces:

* the class exists
* it implements `InteractorInterface`

Example (vendor Blog module):

```php
Lapis::interactorRegistry()->set(
    'vendor.ampas.blog',
    BlogInteractor::class
);
```

### Why Interactors matter

Interactors prevent architectural erosion by avoiding direct coupling like:

* Module A importing Module B internal classes
* Module A calling Module B Entities directly

Instead, Module B exposes a stable contract (Interactor), and other modules depend on that contract.

---

## Configuring Modules in the Child Application

Child apps configure modules in:

* `app/config/modules.php`

Example (from the skeleton app):

```php
return [
    'Catalog' => [
        'source'   => 'app',
        'enabled'  => false,
        'priority' => 1001,
    ],
    'Blog' => [
        'source'  => 'vendor',
        'enabled' => true,
    ],
];
```

### How overriding works

When `discoverAppModules()` sees a module with `source: vendor` or `source: core`, it builds a composite key like:

```
vendor.<ModuleKey>
core.<ModuleKey>
```

If that composite key already exists (because it was discovered earlier), it updates:

* `enabled`
* `priority`

This means a child app can:

* disable a core module
* change load order
* enable/disable vendor modules

### Important naming rule (to avoid confusion)

Your child app’s module key must match the module’s actual `module-key`.

For example, your vendor Blog module declares:

```json
"lapis-module": {
  "module-key": "Blog"
}
```

So the child config should refer to:

```php
'Blog' => [
  'source' => 'vendor',
  'enabled' => true,
]
```

> **Recommendation:** Use short module keys like `Blog`, `Catalog`, `Security`. Avoid suffixing `Module` in keys, because the framework already expects the runtime class name to be `<ModuleKey>Module`.

---

## Creating an App Module (inside the child project)

1. Create:

```
app/Modules/Catalog/
  CatalogModule.php
  CatalogRoutes.php
  CatalogUIs.php
```

2. Implement module hooks:

```php
namespace App\Modules\Catalog;

use BitSynama\Lapis\Framework\Contracts\ModuleInterface;

final class CatalogModule implements ModuleInterface
{
    public static function registerHandlers(): void {}
    public static function registerRoutes(): void { CatalogRoutes::register(); }
    public static function registerUIs(): void { CatalogUIs::register(); }
}
```

3. Enable it in `app/config/modules.php`:

```php
return [
    'Catalog' => [
        'source' => 'app',
        'enabled' => true,
        'priority' => 200,
    ],
];
```

---

## Creating a Vendor Module (Composer-installable)

Vendor modules must declare:

### 1) Composer package type

```json
"type": "bitsynama-lapis-module"
```

### 2) Module metadata

```json
"extra": {
  "lapis-module": {
    "module-key":  "Blog",
    "module-path": "src",
    "priority":    150
  }
}
```

### 3) PSR-4 autoload mapping

```json
"autoload": {
  "psr-4": { "Ampas\\Blog\\": "src/" }
}
```

### 4) Module class

The module class must exist at:

```
<AUTLOAD_NAMESPACE><ModuleKey>Module
```

Example:

* Namespace: `Ampas\Blog\`
* ModuleKey: `Blog`
* Module class: `Ampas\Blog\BlogModule`

---

## Summary

* Modules are the primary extensibility boundary in Lapis Orkestra.
* They are discovered from **core**, **vendor**, and **app** sources.
* Load order is controlled by `priority`.
* Modules register:

  * handlers (registries)
  * routes
  * UIs
  * Interactors provide contract-based module communication.

---

## Next Document

Proceed to [**CONFIG.md**](../docs/CONFIG.md) to read on:

* how module defaults are merged
* how environment overrides work
* how registries and config interact during boot

---

[← Back to Architecture README](../docs/ARCHITECTURE.md)
[← Back to Main README](../README.md)
