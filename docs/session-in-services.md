# Getting SessionInterface in Services/Resolvers

## The Problem

You can get `SessionInterface` in handlers like this:
```php
$session = $request->getAttribute(SessionInterface::class);
```

But you **cannot inject** `SessionInterface` into service constructors via dependency injection because:
- Session is **request-specific** (created per HTTP request)
- The DI container creates services once at application startup
- Services are **shared** across all requests

## The Solution: Pass as Method Parameter

Instead of constructor injection, pass the session as a method parameter:

### ✅ Correct Approach (Method Parameter)

```php
final class RedirectResolver
{
    public function resolve(User $user, SessionInterface $session): string
    {
        // Now you can use $session
        $intended = $session->get('intended_url');
        
        if (is_string($intended) && str_starts_with($intended, '/')) {
            $session->unset('intended_url');
            return $intended;
        }
        
        return '/default';
    }
}
```

### Usage in Handler

```php
public function handle(ServerRequestInterface $request): ResponseInterface
{
    $session = $request->getAttribute(SessionInterface::class);
    $user = $this->userRepository->find($userId);
    
    // Pass session to the resolver
    $redirect = $this->redirectResolver->resolve($user, $session);
    
    return new RedirectResponse($redirect);
}
```

### ❌ Incorrect Approach (Constructor Injection)

```php
// DON'T DO THIS - Won't work!
final class RedirectResolver
{
    public function __construct(
        private SessionInterface $session  // ❌ This won't work!
    ) {}
}
```

## Why This Pattern?

Request-specific objects should be passed as method parameters:
- ✅ `SessionInterface` - request-specific
- ✅ `ServerRequestInterface` - request-specific
- ✅ `ResponseInterface` - request-specific

Application-level dependencies should be injected via constructor:
- ✅ `EntityManagerInterface` - application-level
- ✅ `LoggerInterface` - application-level
- ✅ Repositories - application-level
- ✅ Configuration - application-level

## Your Updated Code

### RedirectResolver.php
```php
final class RedirectResolver
{
    public function resolve(User $user, SessionInterface $session): string
    {
        // Check for intended URL
        $intended = $session->get('intended_url');
        
        if (is_string($intended) && str_starts_with($intended, '/')) {
            $session->unset('intended_url');
            return $intended;
        }

        // Default redirect based on user scope
        return match ($user->getScope()) {
            'platform'  => '/platform',
            'dashboard' => '/dashboard',
            default     => '/',
        };
    }
}
```

### GoogleCallbackHandler.php
```php
$session = $request->getAttribute(SessionInterface::class);
$user = $this->userResolver->resolve($identity);

// Pass session to resolver
$redirect = $this->redirectResolver->resolve($user, $session);
```

## Summary

✅ **Get session in handler**: `$request->getAttribute(SessionInterface::class)`  
✅ **Pass to resolver/service**: As a method parameter  
❌ **Don't inject in constructor**: Session is request-specific, not application-level  

This pattern keeps your code clean and follows Mezzio/PSR-15 best practices.

