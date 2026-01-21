# ADR-005: Monorepo Repository Strategy for Small Team

## Status
Accepted

## Date
2026-01-21

## Authors
- Vasil Dakov

## Context
As a small team building a modularized application with multiple modules (User, Album, API, etc.) and shared infrastructure (MySQL, MongoDB, Redis, RabbitMQ), we need to decide on a repository strategy. The choice between monorepo and multi-repo affects collaboration, code sharing, versioning, and deployment workflows.

## Decision
We will use a **monorepo** structure where all application modules, shared libraries, configuration, and infrastructure code reside in a single Git repository.

### Repository Structure

```
project-root/ 
├── modules/ 
│ ├── User/ 
│ ├── Album/ 
│ └── Api/ 
├── config/ 
├── docs/ 
│ └── adr/ 
├── tests/ 
├── tools/ 
│ ├── phpstan.neon 
│ ├── phpcs.xml 
│ └── phpunit.xml 
├── docker/ 
├── .github/ 
│ └── workflows/ 
├── composer.json 
└── README.md
```

### Key Principles
- Single `composer.json` at root manages all dependencies
- Shared tooling configuration (PHPStan, PHPCS, PHPUnit)
- Unified CI/CD pipeline
- Single source of truth for all code
- Atomic commits across module boundaries
- Consistent versioning across modules

## Consequences
**Positive:**
- **Simplified dependency management**: Single `composer.json` for entire codebase
- **Atomic changes**: Cross-module refactoring in single commit/PR
- **Easier code sharing**: Shared utilities and interfaces without versioning complexity
- **Unified CI/CD**: Single pipeline configuration and deployment process
- **Better collaboration**: Small team sees all code, easier knowledge sharing
- **Consistent tooling**: One set of QA tools, coding standards, and configurations
- **Simplified local development**: Clone once, run entire stack
- **Easier refactoring**: Cross-module changes don't require version coordination
- **Single version**: Entire application versioned together, no compatibility matrix

**Negative:**
- Repository size grows with all modules and history
- CI/CD runs all tests even for single module changes (mitigated with smart caching)
- Cannot independently version modules
- All developers have access to all code
- Git operations may slow down as repository grows
- Module boundaries may become blurred without discipline

## Alternatives Considered
- **Multi-repo (polyrepo)**: Rejected for small team due to coordination overhead
    - Requires versioning and compatibility management between modules
    - Complex dependency updates across repositories
    - Harder to make atomic cross-module changes
    - Overhead not justified for team size
- **Hybrid approach**: Rejected as premature optimization
    - Extract modules to separate repos only when teams grow
    - Can migrate later if needed

## Implementation Notes

### Module Isolation
- Each module maintains clear boundaries despite monorepo
- Modules communicate via defined interfaces
- No direct cross-module dependencies on internal implementations
- Use PSR-11 container for dependency injection between modules

### CI/CD Optimization
- Implement path-based filtering for test execution
- Use build caching for dependencies and artifacts
- Run module-specific tests only when module code changes
- Full integration tests on main branch and releases

### Composer Autoloading
```json
{
  "autoload": {
    "psr-4": {
      "App\\": "src/",
      "User\\": "modules/User/src/",
      "Album\\": "modules/Album/src/",
      "Api\\": "modules/Api/src/"
    }
  }
}
