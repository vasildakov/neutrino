# PHPStan Template Fixes

## Issue
PHPStan was reporting errors in the `view.phtml` template:

1. **Variable $this might not be defined** - When using `$this->escapeHtml()`
2. **Cannot call method on string** - For `$role->name()` and `$role->getScope()`
3. **Cannot call method toString() on UuidInterface|string** - For UUID objects

## Fixes Applied

### 1. Fixed `$this` Undefined Error

**Problem:**
```php
<?= $this->escapeHtml($activity->getDescription()) ?>
```

PHPStan couldn't determine if `$this` was defined in the template context.

**Solution:**
Added a local escape function at the top of the template:

```php
// Helper function for escaping HTML
$esc = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
```

Then replaced all instances:
```php
<?= $esc($activity->getDescription()) ?>
<?= $esc($activity->getCity() ?? '-') ?>
<?= $esc($activity->getIpAddress() ?? '-') ?>
<?= $esc($activity->getBrowser()) ?>
```

### 2. Fixed Role Type Inference

**Problem:**
```php
<?php foreach($user->getRoles() as $role): ?>
    <td><?=$role->name()?></td>
    <td><?=$role->getScope()?></td>
<?php endforeach; ?>
```

PHPStan couldn't infer that `$role` was a `Role` object.

**Solution:**
Added type annotation before the foreach:

```php
<?php
/** @var \Neutrino\Domain\User\Role $role */
foreach($user->getRoles() as $role): ?>
    <td><?=$role->name()?></td>
    <td><?=$role->getScope()?></td>
<?php endforeach; ?>
```

### 3. Fixed UUID toString() Error

**Problem:**
```php
<?=substr($membership->account()->id()->toString(), 0, 8)?>
<?=substr($payment->id()->toString(), 0, 8)?>
```

PHPStan detected that `id()` returns `UuidInterface|string` and couldn't guarantee `toString()` exists.

**Solution:**
Use string casting instead of calling `toString()`:

```php
<?=substr((string) $membership->account()->id(), 0, 8)?>
<?=substr((string) $payment->id(), 0, 8)?>
```

This works because both `UuidInterface` implements `__toString()` and string values remain unchanged.

## Verification

All PHPStan errors have been resolved at maximum level:

```bash
docker exec -it neutrino_php vendor/bin/phpstan analyze \
  src/Platform/templates/platform/account/view.phtml --level=max

[OK] No errors
```

## Template Variables

The template now has proper type annotations:

```php
/**
 * @var \Neutrino\Domain\User\User $user
 * @psalm-var \Neutrino\Domain\User\User $user
 * @var array<\Neutrino\Domain\User\UserActivity> $activities
 */
```

## Best Practices

1. **Escape Output**: Always use `$esc()` or `htmlspecialchars()` for user-generated content
2. **Type Annotations**: Add `@var` annotations for variables in loops when PHPStan can't infer types
3. **Avoid $this in Templates**: Use local functions instead of relying on view helpers for better static analysis
4. **UUID Conversion**: Use `(string)` casting instead of `->toString()` for UuidInterface|string union types

## Date Fixed
February 13, 2026

