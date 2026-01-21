# ADR-001: Modularized Application Structure

## Status
Accepted

## Date
2026-01-21

## Authors
- Vasil Dakov

## Context
As the application grows, maintaining a monolithic codebase becomes challenging. Features, dependencies, and teams become tightly coupled, making development, testing, and deployment harder. A modular architecture allows for better separation of concerns, easier maintenance, and improved scalability.

## Decision
We will structure the application into distinct modules stored in a `modules/` directory. Each module will encapsulate related functionality (e.g., User, Album, API), and will contain its own configuration, routes, handlers, and dependencies. Modules will be registered with the main application during bootstrap.

- Each module will reside in `modules/ModuleName/`.
- Module-specific routes will be defined in `modules/ModuleName/config/routes.php`.
- Each module will have a `ConfigProvider` class for dependency injection and configuration.
- Shared services and configuration will remain in the main `config/` directory.
- Composer autoloading will be configured to support module namespaces.
- Main `config/routes.php` will delegate to module-specific route files.

## Consequences
**Positive:**
- Improved code organization and maintainability.
- Teams can work on different modules independently.
- Easier to add, remove, or refactor features.
- Clear separation between core application and module-specific code.
- Modules can be developed, tested, and versioned independently.

**Negative:**
- Slightly increased complexity in application bootstrap and configuration.
- Need to manage module dependencies and loading order.
- Requires discipline to maintain module boundaries.

## Alternatives Considered
- Continue with a monolithic structure (rejected due to maintainability concerns).
- Use `src/` directory for modules (rejected to maintain clear separation).
- Use a microservices approach (rejected as premature for current scale).

## Implementation Notes
- Update `composer.json` to autoload modules from `modules/` directory.
- Create a module loader/registry in the bootstrap process.
- Each module follows the structure: `modules/ModuleName/src/`, `modules/ModuleName/config/`.

## References
- [Mezzio Modular Applications](https://docs.mezzio.dev/mezzio/v3/features/modular-applications/)
- [Laminas MVC Module System](https://docs.laminas.dev/laminas-modulemanager/)
