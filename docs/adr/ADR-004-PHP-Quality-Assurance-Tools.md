# ADR-004: PHP Quality Assurance Tools

## Status
Accepted

## Date
2026-01-21

## Authors
- Vasil Dakov

## Context
To maintain high code quality, catch bugs early, and ensure consistent coding standards, we need a comprehensive quality assurance strategy. Automated tools can help enforce best practices, detect potential issues, and maintain test coverage throughout the development lifecycle.

## Decision
We will integrate the following PHP QA tools into our development workflow:

### Static Analysis
- **PHPStan**: Static analysis tool for finding bugs without running code (Level 8+)
- **Psalm**: Additional static analysis with focus on type safety

### Code Style & Quality
- **PHP_CodeSniffer (PHPCS)**: Detects violations of PSR-12 coding standards
- **PHP-CS-Fixer**: Automatically fixes code style issues
- **PHP Mess Detector (PHPMD)**: Detects code smells, unused code, and complexity issues

### Testing
- **PHPUnit**: Unit and integration testing framework
- **Infection**: Mutation testing to verify test quality
- **PHPBench**: Performance benchmarking

### Additional Tools
- **PHP-Parallel-Lint**: Checks PHP syntax in parallel
- **Composer Require Checker**: Verifies composer dependencies are properly declared
- **PHP Metrics**: Code quality metrics and visualizations

## Consequences
**Positive:**
- Early detection of bugs and type errors through static analysis
- Consistent code quality across all modules
- High test coverage with quality tests verified by mutation testing
- Automated code style enforcement reduces review friction
- Performance regression detection through benchmarking
- CI/CD pipeline can automatically enforce quality gates
- Improved developer confidence when refactoring

**Negative:**
- Initial setup and configuration time required
- Learning curve for team members
- Build/CI pipeline execution time increases
- May require refactoring existing code to pass strict checks
- Maintenance overhead for tool configurations

## Alternatives Considered
- **Manual code reviews only**: Rejected as insufficient and error-prone
- **Single tool approach**: Rejected as no single tool covers all aspects
- **Less strict analysis levels**: Rejected to maintain high quality standards

## Implementation Notes

### Composer Dependencies
```json
{
  "require-dev": {
    "phpunit/phpunit": "^10.0",
    "phpstan/phpstan": "^1.10",
    "vimeo/psalm": "^5.0",
    "squizlabs/php_codesniffer": "^3.7",
    "friendsofphp/php-cs-fixer": "^3.0",
    "phpmd/phpmd": "^2.13",
    "infection/infection": "^0.27",
    "phpbench/phpbench": "^1.2",
    "php-parallel-lint/php-parallel-lint": "^1.3",
    "maglnet/composer-require-checker": "^4.0"
  }
}
