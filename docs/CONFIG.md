# Configuration

---

> For full project context, return to [Main README](../README.md)
>
> [← Back to Modules](../docs/MODULES.md)

---

This document explains **how configuration works in Lapis Orkestra**, how values are discovered and merged, and how configuration supports **incremental development and maintainability**.

Configuration in Lapis Orkestra is intentionally:

* explicit (no hidden magic)
* layered (framework → modules → application → environment)
* traceable (all resolved values are observable via registries)

---

## Design Goals

The configuration system is designed to:

1. Allow the framework to provide safe defaults
2. Allow modules to define self-contained configuration
3. Allow the child application to override behaviour without forking
4. Allow environment-specific behaviour (dev / test / prod)
5. Avoid scattered `env()` calls and hard-coded constants

---

## Configuration Layers (Highest to Lowest Priority)

Resolved configuration values follow a **layered override model**:

1. **Runtime overrides** (explicit, programmatic)
2. **Environment configuration** (`.env`, server vars)
3. **Child application configuration** (`app/config/*`)
4. **Module configuration** (core, vendor, app modules)
5. **Framework defaults**

Higher layers override lower layers.

---

## Config Registry

All resolved configuration values are stored in:

```
BitSynama\Lapis\Framework\Registries\ConfigRegistry
```

This registry acts as the **single source of truth** for configuration during runtime.

### Why a registry?

Instead of:

* global constants
* repeated `getenv()` calls
* passing configuration arrays everywhere

Lapis Orkestra centralises configuration so that:

* values are traceable
* values can be inspected during debugging
* runtime behaviour can be reasoned about deterministically

---

## Boot-Time Configuration Flow

Configuration resolution happens during:

```
Lapis::boot()
```

### Step-by-step

1. **Framework defaults are loaded**

   * from `src/config/*`

2. **Core module configuration is merged**

   * modules may register default config values

3. **Vendor module configuration is merged**

   * isolated to the module’s scope

4. **Child application configuration is merged**

   * located in `app/config/*`

5. **Environment overrides are applied**

   * `.env`
   * server environment variables

6. **Final resolved config is stored in ConfigRegistry**

---

## Framework Configuration

Framework-level configuration lives in:

```
src/config/
```

Typical responsibilities:

* runtime mode
* router selection
* database defaults
* cache/session behaviour
* view configuration

These defaults are designed to be **safe but minimal**.

---

## Module Configuration

Modules may provide their own configuration defaults.

### Why modules own config

* prevents config sprawl
* keeps feature behaviour self-contained
* allows modules to be reused across projects

Module config is typically namespaced under the module key:

```php
return [
    'user' => [
        'password_policy' => 'strong',
        'email_verification' => true,
    ],
];
```

---

## Vendor Module Configuration

Vendor modules follow the same rules as core modules:

* config defaults belong to the module
* child apps may override them

This ensures third-party modules do not leak global configuration.

---

## Child Application Configuration

The child application defines overrides in:

```
app/config/
```

This is the **primary extension point** for applications.

Example:

```php
return [
    'database' => [
        'default' => 'mysql',
    ],
    'router' => [
        'adapter' => 'fast-route',
    ],
];
```

Child apps can:

* override framework defaults
* override module defaults
* enable/disable features

without modifying framework or vendor code.

---

## Environment Configuration

Environment-specific behaviour is applied last.

Sources may include:

* `.env` files
* web server environment variables
* container/runtime environment

Environment values override all static configuration layers.

---

## Accessing Configuration at Runtime

Configuration values are accessed exclusively via:

```php
Lapis::configRegistry()->get('key.path');
```

This avoids:

* tight coupling to environment variables
* hidden side effects
* inconsistent access patterns

---

## Configuration and Registries

Configuration works closely with other registries:

* **RouteRegistry** — routes may read config values
* **MiddlewareRegistry** — middleware behaviour may depend on config
* **Service Providers** — provider selection via config
* **Utilities Adapters** — adapter selection via config

This allows runtime behaviour to change **without structural changes**.

---

## Configuration Validation

Currently:

* configuration is trusted
* validation is minimal

Planned improvements:

* config schema validation
* fail-fast behaviour for invalid configs

This is intentionally deferred to keep thesis scope focused.

---

## Why This Supports Maintainability

* Clear override order reduces debugging complexity
* Modules own their own behaviour
* Child apps control final behaviour without forks
* Central registry prevents config scattering

Configuration changes do not require architectural changes.

---

## Next Document

Proceed to [Registries](../docs/REGISTRIES.md) to read on:

* why registries exist
* how each registry differs in responsibility
* why `VarRegistry` replaces global variables

---

[← Back to Modules](../docs/MODULES.md)

[← Back to Main README](../README.md)
