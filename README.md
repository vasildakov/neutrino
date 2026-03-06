# Neutrino

> A modern CMS platform built with Mezzio Framework and Domain-Driven Design principles.

[![PHP Version](https://img.shields.io/badge/php-8.2%20%7C%208.3%20%7C%208.4%20%7C%208.5-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-Proprietary-red.svg)](LICENSE.md)

## About

Neutrino is a multi-tenant CMS platform designed with modern PHP best practices, featuring a modularized architecture and comprehensive quality assurance tools.

**Website**: [neutrino.bg](https://neutrino.bg)  
**Author**: [Vasil Dakov](https://vasildakov.com)

## Features

- 🏗️ **Modular Architecture** - Clean separation of concerns with Platform, Dashboard, and Core modules
- 🎯 **Domain-Driven Design** - Built following DDD principles for maintainability
- 🔐 **Multi-tenant Support** - Manage multiple stores with subdomain routing (e.g., `store.neutrino.bg`)
- 👥 **User & Account Management** - Comprehensive user authentication and authorization
- 📦 **Subscription Plans** - Built-in support for subscription-based services
- 🛡️ **Type Safety** - Leverages PHP 8.2+ features with strict typing

## Requirements

- PHP 8.2, 8.3, 8.4, or 8.5
- Composer
- MySQL/MariaDB (via Doctrine ORM)
- ext-intl

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/vasildakov/neutrino.git
cd neutrino/apps/neutrino
```

### 2. Install dependencies

```bash
composer install
```

### 3. Configure environment

Copy the development configuration files:

```bash
cp config/development.config.php.dist config/development.config.php
cp config/autoload/development.local.php.dist config/autoload/development.local.php
cp config/autoload/local.php.dist config/autoload/local.php
```

### 4. Database setup

Configure your database credentials in `config/autoload/doctrine.local.php`, then run migrations:

```bash
vendor/bin/doctrine-migrations migrate
```

### 5. Start development server

```bash
php -S 0.0.0.0:8080 -t public/
```

Visit `http://localhost:8080` in your browser.

## Project Structure

```
src/
├── Dashboard/          # Admin dashboard module
├── Neutrino/          # Core business logic (Domain, Application, Infrastructure)
└── Platform/          # Public-facing platform module

config/
├── autoload/          # Module-specific configuration
├── routes.php         # Application routes
└── container.php      # Dependency injection container

data/
├── cache/            # Application cache
└── migrations/       # Database migrations
```

## Development

### Code Quality Tools

The project includes comprehensive quality assurance tools:

```bash
# Run PHPStan (static analysis)
composer phpstan

# Run Psalm (static analysis)
composer psalm

# Run PHP CodeSniffer (code style)
composer cs-check

# Fix code style automatically
composer cs-fix

# Run PHPUnit tests
composer test
```

### Architecture Decision Records

Important architectural decisions are documented in the `docs/adr/` directory:

- [ADR-001: Modularized Application Structure](docs/adr/ADR-001-Modularized-Application-Structure.md)
- [ADR-002: Technology Stack Selection](docs/adr/ADR-002-Technology-Stack-Selection.md)
- [ADR-003: PHP PSR Standards Compliance](docs/adr/ADR-003-PHP-PSR-Standards-Compliance.md)
- [ADR-004: PHP Quality Assurance Tools](docs/adr/ADR-004-PHP-Quality-Assurance-Tools.md)
- [ADR-005: Git Monorepo Strategy](docs/adr/ADR-005-Git-Monorepo-Strategy.md)

## Technology Stack

- **Framework**: Mezzio (PSR-7, PSR-15, PSR-11)
- **ORM**: Doctrine ORM
- **Templating**: Laminas View
- **Authentication**: Mezzio Authentication
- **Routing**: Laminas Router
- **Dependency Injection**: Laminas ServiceManager

## Testing

```bash
# Run all tests
composer test

# Run tests with coverage
vendor/bin/phpunit --coverage-html coverage/
```

## Documentation

Additional documentation can be found in the `docs/` directory:

- [Flow Diagrams](docs/flow.md)
- [Class Diagrams](docs/class.diagram.md)
- [Deployment Guide](docs/deployment/readme.md)

## License

This software is proprietary. All rights reserved to Neutrino and Vasil Dakov.

See [LICENSE.md](LICENSE.md) for details.

## Copyright

Copyright (c) 2026 [Neutrino](https://neutrino.bg) and [Vasil Dakov](https://vasildakov.com)

---

**Note**: This is proprietary software. Unauthorized copying, distribution, or use is strictly prohibited.

