# Fix: EntityManager Service Resolution Issue

## Problem

The application was throwing the following error:

```
Laminas\ServiceManager\Exception\ServiceNotFoundException
Unable to resolve service "Doctrine\ORM\EntityManager" to a factory;
are you certain you provided it during configuration?
```

## Root Cause

The VasilDakov Doctrine package (used in this application) only registers `Doctrine\ORM\EntityManagerInterface` in the service container, not the concrete `Doctrine\ORM\EntityManager` class.

Some parts of the codebase (particularly CLI scripts and older code) were trying to inject or resolve `EntityManager` instead of `EntityManagerInterface`.

## Solution

Added an alias in the dependency configuration to map `EntityManager` to `EntityManagerInterface`, allowing both to be used interchangeably.

### Changes Made

**File: `/config/autoload/dependencies.global.php`**

Added the following alias:

```php
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

return [
    'dependencies' => [
        'aliases' => [
            // Alias EntityManager to EntityManagerInterface for backwards compatibility
            EntityManager::class => EntityManagerInterface::class,
        ],
        // ... rest of config
    ],
];
```

This ensures that any code requesting `EntityManager::class` will receive the same instance as code requesting `EntityManagerInterface::class`.

## Verification

### Test 1: Service Resolution
```bash
docker exec -it neutrino_php php bin/test-activities.php
```

Result: ✓ All services resolved successfully

### Test 2: Seeder Script
```bash
docker exec -it neutrino_php php bin/seed-activities.php
```

Result: ✓ Activities created successfully for all users

### Test 3: Repository Access
```bash
docker exec -it neutrino_php php bin/test-activities.php
```

Result: ✓ UserActivityRepository working correctly

## Best Practices Going Forward

1. **Prefer Interface Injection**: Always use `EntityManagerInterface` instead of `EntityManager` in:
   - Constructor parameters
   - Method parameters
   - Container `get()` calls

2. **Type Hints**: Use `EntityManagerInterface` in type hints:
   ```php
   public function __construct(
       private EntityManagerInterface $em  // ✓ Correct
       // NOT: private EntityManager $em   // ✗ Avoid
   ) {}
   ```

3. **Backwards Compatibility**: The alias ensures existing code using `EntityManager` continues to work, but new code should use the interface.

## Related Files

- `/config/autoload/dependencies.global.php` - Main dependency configuration
- `/config/autoload/doctrine.global.php` - Doctrine-specific configuration
- `/vendor/vasildakov/mezzio-doctrine/src/ConfigProvider.php` - Doctrine package config

## Testing

All UserActivity system components verified:
- ✓ Entity Manager resolution
- ✓ Repository resolution
- ✓ Activity creation
- ✓ Activity querying
- ✓ Template rendering

## Date Fixed
February 13, 2026

