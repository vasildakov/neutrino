# OAuth Authentication Session Management - FIXED

## The Problem

After Google OAuth login, the `CheckoutFormHandler` was checking for `user_id` in the session:
```php
$userId = $session->get('user_id');
```

But the `GoogleCallbackHandler` was only setting the `UserInterface` data, not the `user_id` separately. This caused logged-in users to be redirected to `/login` when trying to access checkout.

## The Solution

Updated `GoogleCallbackHandler` to store BOTH pieces of data in the session, matching the regular `LoginHandler` behavior:

### GoogleCallbackHandler (After OAuth Success)

```php
// Regenerate session for security (before setting auth data)
$session->regenerate();

// Set authentication data (this is what PhpSession reads)
$session->set(\Mezzio\Authentication\UserInterface::class, [
    'username' => $user->getIdentity(),
    'roles'    => $user->getRolesNames(),
    'details'  => $user->getDetails(),
]);

// Store user_id separately (for consistency with regular login)
$session->set('user_id', $user->getIdentity());  // ✅ ADDED THIS!

$this->logger->info('User authenticated via Google OAuth', [
    'email' => $user->getEmail(),
    'identity' => $user->getIdentity(),
]);
```

## Session Data After Login

After successful OAuth or regular login, the session now contains:

```php
[
    // For Mezzio authentication middleware
    'Mezzio\Authentication\UserInterface' => [
        'username' => '01234567-89ab-cdef-0123-456789abcdef',
        'roles' => ['user'],
        'details' => ['scope' => 'platform'],
    ],
    
    // For handlers that need quick user access
    'user_id' => '01234567-89ab-cdef-0123-456789abcdef',
]
```

## How to Check Authentication in Handlers

You now have **two ways** to check if a user is authenticated:

### Method 1: Via UserInterface (Preferred for most cases)
```php
use Mezzio\Authentication\UserInterface;

$userInterface = $request->getAttribute(UserInterface::class);

if ($userInterface instanceof UserInterface) {
    // User is authenticated
    $userId = $userInterface->getIdentity();
}
```

### Method 2: Via session user_id (Used in checkout flow)
```php
use Mezzio\Session\SessionInterface;

$session = $request->getAttribute(SessionInterface::class);
$userId = $session->get('user_id');

if ($userId) {
    // User is authenticated
    $user = $this->userRepository->find($userId);
}
```

## Why Both Methods?

- **UserInterface**: Used by Mezzio's authentication system and middleware
- **user_id**: Quick access for handlers that need the user ID directly (like checkout, cart)

Both methods work after OAuth login or regular login!

## Files Changed

✅ `/src/Authentication/src/Handler/Google/GoogleCallbackHandler.php`
- Added `$session->set('user_id', $user->getIdentity());`
- Added logging of authentication event

## What Works Now

✅ Google OAuth login sets both session keys  
✅ Regular login sets both session keys  
✅ `CheckoutFormHandler` can check `$session->get('user_id')`  
✅ All handlers can use `$request->getAttribute(UserInterface::class)`  
✅ Authentication persists across requests  
✅ Protected routes work correctly  

## Logout Handling

When user logs out, both keys are cleared:

```php
// In LogoutHandler
$session->unset(UserInterface::class);
$session->unset('user_id');
$session->regenerate();
```

## Summary

Your OAuth authentication now fully mirrors the regular login behavior. Users authenticated via Google OAuth will have the same session structure as users who log in with email/password, ensuring all handlers work consistently regardless of the authentication method used.

