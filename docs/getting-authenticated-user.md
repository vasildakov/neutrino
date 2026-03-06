# Getting the Authenticated User in Mezzio

After a user is authenticated (via regular login or OAuth), you can retrieve them in several ways depending on your needs.

## Method 1: Get UserInterface from Request Attribute (Recommended for most cases)

The authenticated user is automatically injected into the request by Mezzio's authentication middleware.

```php
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ServerRequestInterface;

public function handle(ServerRequestInterface $request): ResponseInterface
{
    // Get the authenticated user (returns null if not authenticated)
    $userInterface = $request->getAttribute(UserInterface::class);
    
    if ($userInterface instanceof UserInterface) {
        // User is authenticated
        $userId = $userInterface->getIdentity(); // e.g., "user-uuid-here"
        $roles = $userInterface->getRoles();     // e.g., ['user', 'admin']
        $details = $userInterface->getDetails(); // e.g., ['scope' => 'platform']
        
        // Access specific detail
        $scope = $userInterface->getDetail('scope'); // e.g., 'platform'
    } else {
        // User is not authenticated (guest)
    }
}
```

## Method 2: Get User Entity from UserRepository

If you need the full `User` entity (not just the interface), use the repository:

```php
use Mezzio\Authentication\UserInterface;
use Neutrino\Repository\UserRepository;
use Neutrino\Domain\User\User;

public function __construct(
    private UserRepository $userRepository,
) {}

public function handle(ServerRequestInterface $request): ResponseInterface
{
    $userInterface = $request->getAttribute(UserInterface::class);
    
    if ($userInterface instanceof UserInterface) {
        // Get the full User entity
        $user = $this->userRepository->find($userInterface->getIdentity());
        
        if ($user instanceof User) {
            // Now you have access to all User entity methods
            $email = $user->getEmail();
            $name = $user->getName();
            // etc.
        }
    }
}
```

## Method 3: Get user_id from Session (Alternative)

Some handlers store `user_id` separately in the session for convenience:

```php
use Mezzio\Session\SessionInterface;

public function handle(ServerRequestInterface $request): ResponseInterface
{
    $session = $request->getAttribute(SessionInterface::class);
    $userId = $session->get('user_id'); // Returns UUID string or null
    
    if ($userId) {
        $user = $this->userRepository->find($userId);
        // Use $user
    }
}
```

## Complete Example: Protected Handler

Here's a complete example of a protected handler that requires authentication:

```php
<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Template\TemplateRendererInterface;
use Neutrino\Domain\User\User;
use Neutrino\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class MyProtectedHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template,
        private UserRepository $userRepository,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Get authenticated user interface
        $userInterface = $request->getAttribute(UserInterface::class);
        
        if (!$userInterface instanceof UserInterface) {
            // This shouldn't happen if AuthenticationMiddleware is configured properly
            // but it's good to check
            return new RedirectResponse('/login');
        }
        
        // Get the full User entity
        $user = $this->userRepository->find($userInterface->getIdentity());
        
        if (!$user instanceof User) {
            // User was deleted but session still exists
            return new RedirectResponse('/login');
        }
        
        // Now use the user
        return new HtmlResponse($this->template->render('app::protected', [
            'user' => $user,
            'email' => $user->getEmail(),
            'roles' => $userInterface->getRoles(),
        ]));
    }
}
```

## How Authentication Works

1. **Login/OAuth**: When a user logs in (via `LoginHandler` or `GoogleCallbackHandler`), the session is populated:
   ```php
   $session->set(\Mezzio\Authentication\UserInterface::class, [
       'username' => $user->getIdentity(),
       'roles'    => $user->getRolesNames(),
       'details'  => $user->getDetails(),
   ]);
   ```

2. **Middleware**: On subsequent requests, `Mezzio\Authentication\AuthenticationMiddleware` reads from session and creates a `UserInterface` instance, injecting it into the request attributes.

3. **Your Handlers**: You access the user via `$request->getAttribute(UserInterface::class)`.

## Protected Routes

To require authentication for a route, add `AuthenticationMiddleware` to the route:

```php
// In config/routes.php
use Mezzio\Authentication\AuthenticationMiddleware;

$app->get('/protected', [
    AuthenticationMiddleware::class,
    App\Handler\MyProtectedHandler::class,
]);
```

If the user is not authenticated, they'll be redirected to `/login` (configured in `config/autoload/authentication.global.php`).

## Summary

- **Quick check**: `$request->getAttribute(UserInterface::class)` - lightweight, always available
- **Full entity**: `$userRepository->find($userInterface->getIdentity())` - when you need domain methods
- **Session fallback**: `$session->get('user_id')` - alternative approach, but less standard

Choose the method that fits your use case. For most handlers, Method 1 (UserInterface from request) is sufficient. Use Method 2 when you need access to domain-specific functionality.

