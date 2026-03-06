# Session Key Renamed: user_id → identity

## Summary

Renamed the session key from `user_id` to `identity` throughout the application for better consistency and clarity.

## Files Changed

### 1. GoogleCallbackHandler.php
```php
// Before:
$session->set('user_id', $user->getIdentity());

// After:
$session->set('identity', $user->getIdentity());
```

### 2. LoginHandler.php
```php
// Before:
$session->set('user_id', $authUser->getIdentity());

// After:
$session->set('identity', $authUser->getIdentity());
```

### 3. ViewCartHandler.php
```php
// Before:
$userId = $session->get('user_id');
// Find user by email (since session stores email as user_id)

// After:
$userId = $session->get('identity');
// Find user by email (since session stores email as identity)
```

### 4. AddToCartHandler.php
```php
// Before:
$userId = $session->get('identity');
// Find user by email (since session stores email as user_id)

// After:
$userId = $session->get('identity');
// Find user by email (since session stores email as identity)
```

### 5. CheckoutFormHandler.php
```php
// Before:
$userId = $session->get('user_id');
// Find user by email (since session stores email as user_id)
$session->unset('user_id');

// After:
$userId = $session->get('identity');
// Find user by email (since session stores email as identity)
$session->unset('identity');
```

## Why This Change?

1. **Consistency**: The session key now matches what the value represents - the user's identity (email)
2. **Clarity**: `identity` is more descriptive than the generic `user_id`
3. **Alignment**: Better aligns with Mezzio's authentication terminology where `getIdentity()` is the standard method

## Session Structure After Login

```php
[
    // Mezzio authentication data
    'Mezzio\Authentication\UserInterface' => [
        'username' => 'vasildakov@gmail.com',  // email (identity)
        'roles' => ['user'],
        'details' => ['scope' => 'platform'],
    ],
    
    // Quick access to user identity
    'identity' => 'vasildakov@gmail.com',  // ✅ Renamed from 'user_id'
]
```

## How to Get Authenticated User

### Method 1: Via Session (Quick)
```php
$identity = $session->get('identity');
if ($identity) {
    $user = $this->userRepository->findOneByEmail($identity);
}
```

### Method 2: Via UserInterface (Mezzio Standard)
```php
$userInterface = $request->getAttribute(UserInterface::class);
if ($userInterface) {
    $identity = $userInterface->getIdentity();
    $user = $this->userRepository->findOneByEmail($identity);
}
```

## Note

Database column names (e.g., `user_id` in foreign keys) remain unchanged as they refer to the database schema, not session keys.

