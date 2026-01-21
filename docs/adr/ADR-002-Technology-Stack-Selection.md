# ADR-002: Technology Stack Selection

## Status
Accepted

## Date
2026-01-21

## Authors
- Vasil Dakov

## Context
We need to establish a consistent technology stack for building a scalable, maintainable web application. The stack should support modular architecture, handle both relational and document-based data, enable caching, and provide reliable messaging capabilities.

## Decision
We will use the following technology stack:

### Application Framework
- **PHP 8.x**: Modern language features, strong typing, performance improvements
- **Mezzio**: PSR-15 middleware framework for flexible request handling

### Data Persistence
- **Doctrine ORM**: Object-relational mapping for MySQL database interactions
- **Doctrine ODM**: Object-document mapping for MongoDB interactions
- **MySQL**: Primary relational database for structured data
- **MongoDB**: Document database for flexible schema requirements

### Caching & Session
- **Redis**: In-memory data store for caching and session management

### Messaging
- **RabbitMQ**: Message broker for asynchronous processing and event-driven architecture

## Consequences
**Positive:**
- Mezzio provides lightweight, PSR-compliant middleware pipeline
- Doctrine ORM/ODM offer consistent API for different data stores
- MySQL ensures ACID compliance for critical transactional data
- MongoDB enables flexible schema for evolving data models
- Redis delivers high-performance caching and reduces database load
- RabbitMQ enables reliable asynchronous communication between modules
- Stack supports both microservices and modular monolith patterns

**Negative:**
- Learning curve for teams unfamiliar with Mezzio or Doctrine
- Operational complexity of managing multiple databases and services
- Need for proper infrastructure and monitoring tools
- Potential data consistency challenges across different storage systems

## Alternatives Considered
- **Laravel**: Rejected in favor of Mezzio for lighter footprint and middleware flexibility
- **Symfony**: Rejected due to heavier framework overhead
- **PostgreSQL**: Considered but MySQL chosen for team familiarity
- **Kafka**: Rejected in favor of RabbitMQ for simpler operational model
- **Memcached**: Redis chosen for richer feature set beyond caching

## Implementation Notes
- Use Doctrine migrations for MySQL schema management
- Configure Redis as session handler and cache backend
- Set up RabbitMQ exchanges and queues per module requirements
- Use connection pooling for all database connections
- Implement proper health checks for all external services
- Document connection configurations in module `ConfigProvider` classes

## References
- [Mezzio Documentation](https://docs.mezzio.dev/)
- [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html)
- [Doctrine MongoDB ODM](https://www.doctrine-project.org/projects/mongodb-odm.html)
- [RabbitMQ Documentation](https://www.rabbitmq.com/documentation.html)
- [Redis Documentation](https://redis.io/documentation)
