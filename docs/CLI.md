# CLI (Console Execution)

---

> For full project context, return to [Main README](../README.md)
> [← Back to Security README](../docs/SECURITY.md)

---

This document describes the **console (CLI) execution model** in Lapis Orkestra and how it mirrors the web runtime while remaining independent of HTTP concerns.

The CLI layer is an important part of the framework’s **maintainability and incremental development strategy**, because it enables:

* reuse of business logic outside HTTP
* operational tooling (maintenance, migration, diagnostics)
* safer automation without controller coupling

---

## Console Entry Point

The console entry point is:

```php
BitSynama\Lapis\LapisConsole::run();
```

It is typically invoked from:

```
bin/console
```

This design intentionally parallels the web entry point:

* Web: `Lapis::run()`
* Console: `LapisConsole::run()`

Both entry points share the same **boot sequence**.

---

## Shared Boot Process

Both web and console runtimes execute:

```
Lapis::boot()
```

This ensures:

* configuration is resolved consistently
* modules are discovered and registered
* registries are initialised
* utilities and services are available

The only difference is the **runtime context** (HTTP vs CLI).

---

## Console Runtime Flow

The high-level console lifecycle is:

```
bin/console
  ↓
LapisConsole::run()
  ↓
Lapis::boot()
  ↓
Console Application Setup
  ↓
Command Discovery & Registration
  ↓
Command Execution
```

---

## Console Framework

Lapis Orkestra uses **Symfony Console** as the underlying CLI framework.

This choice:

* avoids reinventing CLI argument parsing
* provides structured commands, options, and help
* keeps CLI concerns isolated from HTTP routing

---

## Command Discovery

Commands are discovered automatically using:

* `Framework\Loaders\CommandAutoLoader`

Discovery uses the same directory-scanning mechanism employed elsewhere in the framework.

### Discovery Sources

Commands may exist in:

1. Framework commands
2. Core module commands
3. Vendor module commands
4. Child application commands

This allows incremental extension of CLI capabilities.

---

## Directory Conventions

Commands are discovered from directories following this convention:

```
<Scope>/Commands/
```

Examples:

* `src/Framework/Commands/*`
* `src/Modules/*/Commands/*`
* `app/Commands/*`

Commands must extend Symfony’s `Command` class.

---

## Command Registration

Discovered commands are:

* instantiated
* registered into the Symfony Console application
* made available automatically via `bin/console`

No central command list is required.

---

## Reusing Business Logic in CLI

One of the key architectural benefits of the CLI layer is **reuse of Actions**.

### Why Actions matter

Actions are:

* independent of HTTP transport
* focused on business logic
* reusable across contexts

This allows CLI commands to:

* call the same Actions used by Controllers
* avoid duplicating domain logic
* remain thin and maintainable

---

## Example Pattern

A typical CLI command may:

1. parse arguments and options
2. construct input data
3. call an Action
4. handle the returned domain result
5. output to console

This mirrors HTTP execution without involving Controllers, Checkers, or ResponseFilters.

---

## Security Considerations in CLI

CLI execution:

* bypasses HTTP middleware
* does not involve cookies or CSRF

However:

* authentication or permission checks may still be applied manually
* Verifiers may be invoked when required

This allows CLI commands to remain powerful while still respecting security boundaries.

---

## Incremental Development Benefits

The CLI layer supports incremental development by:

* enabling safe administrative tooling
* allowing new commands to be added via modules
* reusing business logic without duplication
* keeping operational code out of controllers

---

## Maintainability Impact (Thesis rationale)

CLI integration improves maintainability because:

* business logic is not tied to HTTP
* operational tasks do not pollute application controllers
* modules can ship their own commands
* debugging and maintenance can be automated

---

## Summary

* CLI execution uses `LapisConsole::run()`
* Boot process is shared with web runtime
* Commands are auto-discovered
* Actions enable business logic reuse
* CLI and HTTP remain cleanly separated

---

## Next Document

Proceed to [**TESTING.md**](../docs/TESTING.md) to read on:

* current testing status
* reasons for deferring tests
* planned testing strategy
  
---

[← Back to Security README](../docs/SECURITY.md)
[← Back to Main README](../README.md)
