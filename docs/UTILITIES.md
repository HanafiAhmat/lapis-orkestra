# Utilities

---

> For full project context, return to [Main README](../README.md)
> 
> [← Back to Registries](../docs/REGISTRIES.md)

---

Utilities in Lapis Orkestra are the **internal infrastructure layer**. They are responsible for interacting with:

* runtime and server environment
* request construction
* routing engines
* session/cookies
* caching
* view rendering
* logging

Utilities are intentionally separated from **Services**:

* **Utilities** → internal or server-facing concerns, implemented via **Adapters**
* **Services** → external systems (email/SMS/3rd-party APIs), implemented via **Providers**

This document focuses on **Utilities**.

---

## Why Utilities exist

In long-lived systems, infrastructure choices change:

* routing library swaps
* PSR-7 request factories change
* template engines evolve
* caching backend changes
* logging tooling changes

If the application couples directly to these implementation details, incremental change becomes expensive.

Lapis Orkestra solves this by introducing **Utilities** with **pluggable adapters**.

This enables:

* incremental refactors (swap adapters without rewriting business code)
* controlled integration points (all internal dependencies go through utilities)
* clearer maintenance boundaries (debugging starts from utility + adapter)

---

## Utility Architecture

A typical utility follows a consistent pattern:

1. A `*Utility` class acts as the façade
2. An adapter interface defines the contract
3. Multiple adapters implement the interface
4. One adapter is selected through configuration
5. Discovery is automatic (framework + modules + child app)

### Example structure

```
src/Utilities/
  CacheUtility.php
  Contracts/CacheAdapterInterface.php
  Adapters/Cache/FileCacheAdapter.php
  Adapters/Cache/ApcuCacheAdapter.php
```

---

## Adapter metadata: `AdapterInfo`

All adapters are annotated using a PHP Attribute:

* `BitSynama\Lapis\Utilities\AdapterInfo`

The attribute provides:

* `type` — the adapter category (e.g. `router`, `cache`)
* `key` — the adapter identifier (e.g. `fastroute`, `file_simple`)
* `description` — optional documentation

Example (router adapter):

```php
#[AdapterInfo(type: 'router', key: 'fastroute', description: 'FastRoute Router')]
final class FastRouteRouterAdapter implements RouterAdapterInterface
{
    // ...
}
```

This keeps adapter selection explicit and discoverable.

---

## Adapter discovery: `Atlas::discover()`

Adapter selection uses the same discovery mechanism across the framework:

* `BitSynama\Lapis\Framework\Foundation\Atlas::discover()`

### What `Atlas::discover()` does

Given:

* a directory path (e.g. `Utilities.Adapters.Router`)
* an interface (e.g. `RouterAdapterInterface`)
* an attribute (e.g. `AdapterInfo`)
* a type/key pair

Atlas scans known directories and returns the **first matching class**:

* class exists
* implements the required interface
* has the required attribute
* attribute `type` and `key` match

### Where Atlas scans

Atlas can discover adapters from:

1. **Framework Utilities**

   * `repo/src/<dirPath>`

2. **Framework internal** (`src/Framework/<dirPath>`) if applicable

3. **Enabled modules** (core/vendor/app)

   * `<modulePath>/<dirPath>`

4. **Child application overrides**

   * `project/app/<dirPath>`

This scanning design allows incremental extension:

* you can ship new adapters inside modules
* a child app can define its own adapters without forking the framework

---

## Utility configuration

Utilities are configured via:

* `src/config/utility.php`

The default configuration uses `Env::*()` helpers (e.g. `Env::string`) to support environment overrides.

### Key utility config paths

These are stored under `ConfigRegistry` as:

* `utility.cache`
* `utility.cookie`
* `utility.http_client`
* `utility.logger`
* `utility.request`
* `utility.router`
* `utility.session`
* `utility.view`

The config controls which adapter key is selected.

Examples from `src/config/utility.php`:

* `UTILITY_ROUTER_ADAPTER` default: `fastroute`
* `UTILITY_VIEW_PROVIDER` default: `plates`
* cache adapter default: `file_simple`

---

## Built-in Utilities and Available Adapters

Below are the utilities currently included in the framework, with their adapter keys.

> Adapter keys are declared through `#[AdapterInfo(...)]`.

### CacheUtility

Contracts:

* `CacheAdapterInterface` (PSR-6)
* `SimpleCacheAdapterInterface` (PSR-16)

Adapters:

* `file` — File system caching
* `file_simple` — File cache implementing Simple Cache
* `apcu` — APCU caching
* `apcu_simple` — APCU implementing Simple Cache

Notes:

* CacheUtility ensures cache directory readiness (defaults into `tmp/caches` when unset)

---

### CookieUtility

Contract:

* `CookieAdapterInterface`

Adapters:

* `lapis` — Default cookie handler

Notes:

* cookie params are configured via `utility.cookie.params` (secure, httponly, samesite, domain, path)

---

### HttpClientUtility

Contract:

* `HttpClientAdapterInterface`

Adapters:

* `guzzle` — Uses `GuzzleHttp\Client`
* `symfony` — Uses Symfony HTTP client PSR-18 client

---

### LoggerUtility

Contract:

* `LoggerAdapterInterface`

Adapters:

* `lapis` — Custom minimal PSR-3 logger
* `monolog` — Monolog PSR-3 logger

Notes:

* logger behaviour is configured via `utility.logger.*` (level, channel, logs_dir)

---

### RequestUtility

Contract:

* `RequestAdapterInterface`

Adapters:

* `guzzle` — Guzzle PSR-17/PSR-7 request adapter
* `nyholm` — Nyholm PSR-17 request adapter

---

### RouterUtility

Contract:

* `RouterAdapterInterface`

Adapters:

* `fastroute` — FastRoute router
* `aura` — Aura.Router adapter

Notes:

* router adapter is controlled via `UTILITY_ROUTER_ADAPTER`
* routing operates on structured route definitions in `RouteRegistry`

---

### SessionUtility

Contract:

* `SessionAdapterInterface`

Adapters:

* `aura` — Aura Session adapter

Notes:

* session config supports **variants** (e.g. admin path-based sessions) via `utility.session.variants`

---

### ViewUtility

Contract:

* `ViewAdapterInterface`

Adapters:

* `plates` — PHP Plates templating
* `twig` — Twig engine
* `lapis` — Lapis internal view adapter

Notes:

* view output toggles support `html` and `json` output flags

---

## How a Utility selects an adapter (pattern)

Every utility follows the same steps:

1. Read `repo_dir`, `project_dir`, `tmp_dir` from **VarRegistry**
2. Read relevant config from **ConfigRegistry** (e.g. `utility.router`)
3. Determine adapter key (fallbacks exist)
4. Call `Atlas::discover()` for the adapter
5. Instantiate and return the adapter

This keeps runtime behaviour predictable and centralised.

---

## Adding a new Utility Adapter

You can add adapters from three places:

### Option A — Child application adapter

Create:

```
app/Utilities/Adapters/Router/MyRouterAdapter.php
```

Implement the correct interface:

* `BitSynama\Lapis\Utilities\Contracts\RouterAdapterInterface`

Annotate it:

```php
#[AdapterInfo(type: 'router', key: 'myrouter', description: 'My Router Adapter')]
```

Set configuration:

* `UTILITY_ROUTER_ADAPTER=myrouter`

Atlas will discover it automatically.

---

### Option B — Module adapter (core/app/vendor module)

Modules may ship adapters under:

```
<ModulePath>/Utilities/Adapters/<Type>/
```

This is useful when a module depends on a specific internal integration.

---

### Option C — Vendor module adapter (Composer)

A vendor module can include utility adapters and expose them by:

* shipping them under its module path
* ensuring they implement the correct interface
* annotating with `AdapterInfo`

This allows feature packages to provide their own infrastructure integration.

---

## Maintainability impact (Thesis rationale)

Utilities reduce maintenance cost by:

* isolating technical volatility behind stable interfaces
* enabling incremental replacement through adapter keys
* preventing direct coupling to third-party infra libraries
* supporting overrides in child apps without forking

Utilities also improve debugging:

* selection is explicit via config keys
* the adapter class can be discovered and traced
* runtime behaviour is not hidden inside a generic container

---

## Next Document

Proceed to [Services](../docs/SERVICES.md) to read on:

* Providers vs Adapters
* `ProviderInfo` metadata and discovery
* service selection via configuration
* external system integration boundaries

---

[← Back to Registries](../docs/REGISTRIES.md)

[← Back to Main README](../README.md)
