# Crystal

Crystal is a small, modular PHP framework intended to provide a lightweight, copy-friendly foundation for web applications. Its reusable `Core/` contains the dependency container and router and can be shared by multiple independent websites or services. Application-specific code and optional components are added by each project; the repository's `admin.alpha-code.dev/` directory is an example application, not a requirement.

Crystal does not require Composer to load its framework classes. Composer may still be used if desired for application dependencies or autoloading.

## Repository contents

- `boot.php` — initializes the framework, registers the built-in class map, loads the application bootstrap, and dispatches the matched route.
- `Core/` — dependency container and router.
- `Components/` — optional reusable functionality, including PDO/MySQL access, query helpers, views, responses, authentication, pagination, and an HTTP client.
- `Support/` — small helpers such as UUID and slug utilities.
- `Tests/` — PHPUnit tests.

The example app and a quick-start guide are documented in the repository's [root README](../README.md).

## Requirements

- PHP 8.0 or newer.
- PDO with the MySQL driver enabled when using the database component.

## Tests

Tests are configured in `phpunit.xml`. Run them with PHPUnit installed in your development environment:

```sh
phpunit -c Crystal/phpunit.xml
```

Alternatively, when working from the `Crystal/` directory:

```sh
phpunit -c phpunit.xml
```

## License

See [`LICENSE`](LICENSE).
