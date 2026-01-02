# Services

---

> For full project context, return to [Main README](../README.md)
>
> [← Back to Utilities](../docs/UTILITIES.md)

---

Services in Lapis Orkestra represent the **external integration layer**.

They exist to isolate business and module logic from the volatility of:

* third-party APIs
* email/SMS gateways
* payment providers
* push notification services
* cloud storage services
* analytics/monitoring platforms

In the architecture, **Services are distinct from Utilities**:

* **Utilities** → internal / server concerns, implemented via **Adapters**
* **Services** → external systems, implemented via **Providers**

This document focuses on **Services**, their Provider model, and how they support maintainability.

---

## Why Services exist

External systems change frequently:

* APIs deprecate
* providers change pricing
* credentials rotate
* response formats evolve
* rate limits appear

If business logic calls providers directly, maintenance becomes expensive.

Lapis Orkestra isolates that volatility by:

1. Defining a service *contract* (interface)
2. Implementing the contract via a swappable provider
3. Selecting the provider through configuration

This enables incremental change:

* swap providers without changing business logic
* add new providers without touching existing modules
* migrate external dependencies gradually

---

## Service Architecture

A typical service follows this pattern:

1. A `*Service` class acts as the façade
2. A provider interface defines the contract
3. Providers implement the interface
4. One provider is selected by configuration
5. Providers are discovered automatically (framework/modules/app)

### Example structure

```
src/Services/
  EmailService.php
  Contracts/EmailProviderInterface.php
  Providers/Email/MailFunctionProvider.php
  Providers/Email/SmtpProvider.php
  Providers/Email/SendGridProvider.php
```

---

## Provider metadata: `ProviderInfo`

Providers declare metadata via a PHP Attribute:

* `BitSynama\Lapis\Services\ProviderInfo`

The attribute includes:

* `type` — the service category (e.g. `email`, `sms`, `storage`)
* `key` — the provider identifier (e.g. `mail`, `smtp`, `sendgrid`)
* `description` — optional documentation

Example:

```php
#[ProviderInfo(type: 'email', key: 'smtp', description: 'SMTP Email Provider')]
final class SmtpEmailProvider implements EmailProviderInterface
{
    // ...
}
```

This makes provider selection explicit and discoverable.

---

## Provider discovery

Provider discovery uses the same discovery mechanism as utilities:

* `BitSynama\Lapis\Framework\Foundation\Atlas::discover()`

Given:

* a directory path (e.g. `Services.Providers.Email`)
* an interface (e.g. `EmailProviderInterface`)
* an attribute (e.g. `ProviderInfo`)
* a `type` + `key`

Atlas scans all known locations and returns the first matching provider class.

### Where Atlas scans

Providers can be discovered from:

1. Framework providers
2. Module providers (core/vendor/app)
3. Child application providers (`app/Services/Providers/*`)

This supports incremental extension:

* a module can introduce its own provider
* a child app can override provider behaviour without forking

---

## Service configuration

Services are configured through:

* `src/config/service.php`

The configuration uses `Env::*()` helpers for environment-driven overrides.

### Common config paths

These are stored under `ConfigRegistry` as:

* `service.email`
* `service.sms`
* `service.storage`
* `service.payment`

Example configuration structure:

```php
return [
  'email' => [
    'provider' => Env::string('SERVICE_EMAIL_PROVIDER', 'mail'),
    'from' => Env::string('SERVICE_EMAIL_FROM', 'noreply@example.com'),
  ],
];
```

---

## Built-in Services (Current)

This section documents services that are currently present in the framework and their provider keys.

> Provider keys are declared via `#[ProviderInfo(...)]`.

### EmailService

Purpose:

* sending transactional or system emails

Typical providers:

* `mail` — PHP `mail()`
* `smtp` — SMTP gateway
* `sendgrid` — API-based provider

Thesis emphasis:

* provider swapping demonstrates incremental integration

---

### Notification Services (optional)

Lapis does not force a single unified notification abstraction.

Instead, it encourages explicit services such as:

* `EmailService`
* `SmsService`
* `PushService`

This avoids ambiguous generic “notification” behaviour.

---

## How a Service selects a provider (pattern)

Every service follows these steps:

1. Read required runtime directories from **VarRegistry**
2. Read service configuration from **ConfigRegistry**
3. Determine provider key (fallbacks exist)
4. Call `Atlas::discover()` for the provider
5. Instantiate and return provider instance

This keeps external behaviour controlled and maintainable.

---

## Adding a New Provider

Providers can be added in three ways:

### Option A — Child application provider

Create:

```
app/Services/Providers/Email/MyEmailProvider.php
```

Implement interface:

* `EmailProviderInterface`

Annotate:

```php
#[ProviderInfo(type: 'email', key: 'myemail', description: 'My Email Provider')]
```

Set environment/config:

* `SERVICE_EMAIL_PROVIDER=myemail`

---

### Option B — Module provider

Modules can ship providers under:

```
<ModulePath>/Services/Providers/<Type>/
```

Useful when:

* a module depends on a specific external integration
* provider should ship with the module

---

### Option C — Vendor module provider (Composer)

Vendor modules can ship providers as part of their package.

This allows reusable modules to integrate external systems while still being replaceable.

---

## Maintainability impact (Thesis rationale)

Services improve maintainability by:

* preventing direct coupling to third-party APIs
* enabling incremental provider migration
* keeping credentials and provider choices in configuration
* localising external changes into providers rather than actions

They also reduce risk:

* providers can be swapped or disabled in emergency scenarios
* fallback providers can be used in development/testing

---

## Services vs Utilities (Summary)

| Aspect       | Utilities                      | Services                     |
| ------------ | ------------------------------ | ---------------------------- |
| Purpose      | internal / server interactions | external system integrations |
| Variability  | library/framework swaps        | vendor/API/provider swaps    |
| Pluggability | Adapter                        | Provider                     |
| Metadata     | `AdapterInfo`                  | `ProviderInfo`               |
| Selection    | `utility.*` config             | `service.*` config           |

---

## Next Document

Proceed to [Request Lifecycle](../docs/REQUEST_LIFECYCLE.md) to read on:

* middleware lifecycle and ordering
* middleware keys via registry
* response filters lifecycle and ordering
* response filters keys via registry
* distinction between middleware and response filters

---

[← Back to Utilities](../docs/UTILITIES.md)

[← Back to Main README](../README.md)
