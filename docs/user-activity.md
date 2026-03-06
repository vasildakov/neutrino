# User Activity Tracking

This document describes the User Activity tracking feature in Neutrino.

## Overview

The User Activity feature allows the system to track and log various user actions such as logins, logouts, profile updates, page views, and other important activities.

## Components

### 1. Domain Model

**UserActivity** (`src/Neutrino/src/Domain/User/UserActivity.php`)

An entity that represents a single user activity record with the following properties:

- `id` - Unique identifier (UUID)
- `user` - The user who performed the activity (relationship to User entity)
- `activityType` - Type of activity (login, logout, view, update, password, etc.)
- `description` - Human-readable description of the activity
- `ipAddress` - IP address from which the activity was performed
- `userAgent` - Browser user agent string
- `city` - Geographic location (city)
- `createdAt` - Timestamp when the activity occurred

### 2. Repository

**UserActivityRepository** (`src/Neutrino/src/Repository/UserActivityRepository.php`)

Provides methods to:
- Save activities
- Find activities by user
- Find recent activities (with limit)

### 3. Service Layer

**UserActivityLogger** (`src/Neutrino/src/Domain/User/UserActivityLogger.php`)

A service for logging user activities with convenience methods:

```php
// General logging
$logger->log($user, 'custom', 'Custom activity description', $request);

// Specific activity types
$logger->logLogin($user, $request);
$logger->logLogout($user, $request);
$logger->logProfileUpdate($user, $request);
$logger->logPasswordChange($user, $request);
$logger->logView($user, 'dashboard', $request);
```

## Usage

### Logging Activities

To log a user activity, inject the `UserActivityLogger` service and call the appropriate method:

```php
use Neutrino\Domain\User\UserActivityLogger;

class SomeHandler
{
    public function __construct(
        private UserActivityLogger $activityLogger
    ) {}
    
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute('user');
        
        // Log the activity
        $this->activityLogger->logView($user, 'some-page', $request);
        
        // ... rest of handler logic
    }
}
```

### Displaying Activities

Activities are automatically displayed on the user profile page (`platform::account/view`).

The view shows:
- Activity type (with color-coded badge)
- Description
- City
- IP Address
- Browser (auto-detected from user agent)
- Date & Time

### Activity Types

The following activity types are supported with color-coded badges:

- `login` - Success (green)
- `logout` - Danger (red)
- `update` - Info (blue)
- `view` - Primary (blue)
- `password` - Warning (yellow)
- `create` - Success (green)
- `delete` - Danger (red)
- Custom types default to secondary (gray)

## Database

### Migration

The `user_activities` table is created by migration `Version20260213121115.php`.

### Schema

```sql
CREATE TABLE user_activities (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    description VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_user_activities_user_id (user_id),
    INDEX idx_user_activities_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users (id)
)
```

## Seeding Test Data

To seed sample activity data for testing:

```bash
docker exec -it neutrino_php php bin/seed-activities.php
```

This will create 5-10 random activities for each existing user.

## Future Enhancements

Potential improvements:

1. **Geolocation Service** - Integrate with a service like MaxMind GeoIP2 to automatically detect city from IP address
2. **Activity Filtering** - Add filters to view specific activity types
3. **Activity Search** - Search activities by description, IP, etc.
4. **Activity Analytics** - Charts and graphs showing activity patterns
5. **Activity Retention** - Automatic cleanup of old activities
6. **Real-time Updates** - WebSocket integration for live activity feed
7. **Activity Export** - Export activities to CSV/PDF for auditing
8. **Suspicious Activity Detection** - Flag unusual patterns (e.g., logins from different countries)

## Security Considerations

- IP addresses are stored for audit purposes
- User agents are stored for browser detection and security analysis
- Consider GDPR compliance when storing location data
- Implement retention policies for activity data
- Consider anonymizing old activity data

## Testing

Activities can be tested by:

1. Logging in/out
2. Updating user profiles
3. Changing passwords
4. Viewing different pages

Check the user profile page to see recorded activities.

