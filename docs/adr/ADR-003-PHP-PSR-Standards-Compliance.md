# ADR-003: PHP PSR Standards Compliance

## Status
Accepted

## Date
2026-01-21

## Authors
- Vasil Dakov

## Context
To ensure code consistency, interoperability, and maintainability across the project and with third-party libraries, we need to adopt standardized coding practices. The PHP Framework Interop Group (PHP-FIG) has established PSR standards that are widely adopted in the PHP community.

## Decision
We will adhere to the following PHP PSR standards:

### Code Style & Structure
- **PSR-1**: Basic Coding Standard
- **PSR-12**: Extended Coding Style Guide (replaces PSR-2)

### Autoloading
- **PSR-4**: Autoloader standard for namespace-to-directory mapping

### Interfaces
- **PSR-3**: Logger Interface
- **PSR-6**: Caching Interface
- **PSR-7**: HTTP Message Interface
- **PSR-11**: Container Interface
- **PSR-14**: Event Dispatcher
- **PSR-15**: HTTP Server Request Handlers (Middleware)
- **PSR-16**: Simple Cache
- **PSR-17**: HTTP Factories
- **PSR-18**: HTTP Client

## Consequences
**Positive:**
- Consistent code style across all modules and team members
- Interoperability with third-party libraries and frameworks
- Easier onboarding for new developers familiar with PSR standards
- Mezzio framework is built on PSR-7, PSR-15, and PSR-11
- IDE support and automated tooling for PSR compliance
- Future-proof architecture using industry standards

**Negative:**
- Team members must learn and follow PSR conventions
- Existing code may need refactoring to comply
- Strict adherence may feel restrictive initially

## Alternatives Considered
- **Custom coding standards**: Rejected to avoid reinventing the wheel
- **Framework-specific standards**: Rejected to maintain vendor independence
- **No standards**: Rejected due to inconsistency and maintainability issues

## Implementation Notes
- Use PHP\_CodeSniffer with PSR-12 ruleset for code style enforcement
- Configure PHP-CS-Fixer for automatic code formatting
- Add pre-commit hooks to check PSR compliance
- Configure PHPStorm code style to follow PSR-12
- Use PSR-4 autoloading in `composer.json` for all modules
- Ensure all interfaces implement relevant PSR interfaces where applicable
- Document PSR compliance requirements in project README

## Tooling
```json
{
  "require-dev": {
    "squizlabs/php_codesniffer": "^3.7",
    "friendsofphp/php-cs-fixer": "^3.0"
  }
}
