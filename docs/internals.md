# Testing

This package provides a consistent set of [Composer](https://getcomposer.org/) scripts for local validation.

Tool references:

- [PHPCodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer) for code style checks.
- [PHPStan](https://phpstan.org/) for static analysis.
- [PHPUnit](https://phpunit.de/) for unit tests.

## Code style checks (PHPCodeSniffer)

Run code style checks.

```bash
composer cs
```

Fix code style issues.

```bash
composer cs-fix
```

## Static analysis (PHPStan)

Run static analysis.

```bash
composer static
```

## Unit tests (PHPUnit)

Run tests without group db,wincache

```bash
composer tests
```

Run tests MSSQL database.

```bash
composer mssql
```

Run tests MySQL database.

```bash
composer mysql
```

Run tests Oracle database.

```bash
composer oracle
```

Run tests PostgreSQL database.

```bash
composer pgsql
```

Run tests SQLite database.

```bash
composer sqlite
```

> [!NOTE]
> To run tests for MSSQL, MySQL, Oracle, or PostgreSQL databases, ensure the corresponding database server is running and accessible.


## Passing extra arguments

Composer scripts support forwarding additional arguments using `--`.

Run PHPUnit with code coverage report generation.

```bash
composer tests -- --coverage-html code_coverage
```

Run PHPStan with a different memory limit.

```bash
composer static -- --memory-limit=512M
```
