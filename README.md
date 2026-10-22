# Crystal

Crystal is a small, modular PHP framework for building practical web applications without the setup overhead of a full-stack framework. It is intended to feel familiar to Laravel users while staying lightweight: copy the framework into a project, connect your database, add your routes and app code, and get building.

## What’s included

- **Core** — dependency container and HTTP router.
- **Components** — database access with PDO/MySQL, query helpers, views, responses, authentication, pagination, and HTTP client utilities.
- **Support** — small reusable helpers such as UUID and slug utilities.
- **Application example** — `admin.alpha-code.dev/` demonstrates controllers, services, views, routes, and database configuration.

### Reusable core

`Crystal/Core/` is intended to be the shared, reusable foundation: the container and router can be used across multiple independent websites or services. Each site or service can have its own application code, routes, configuration, and components while sharing that core. The `admin.alpha-code.dev/` app is only an example of one way to build an application on top of Crystal; it is not required to use the core or a template that every project must follow.

Crystal uses a small built-in class map and does not require Composer to load the framework itself. Composer is optional and can be used if desired—for example, to manage application dependencies or provide Composer-based autoloading in your project.

## Project status

Crystal is a work in progress. Current development is focused on expanding the collection of useful, self-contained components—building on pieces such as `Db.php` and `Pager.php`—while keeping the framework small and straightforward to copy into an application.

### To-do

- [ ] Add more reusable, practical framework components.
- [ ] Improve and expand existing components based on real application needs.
- [ ] Refactor `boot.php` into a more modular process with initialization responsibilities separated into components.
- [ ] Document component APIs and provide concise usage examples.
- [ ] Keep the example application and tests up to date as the framework evolves.

## Quick start

1. Copy the `Crystal/` directory into your PHP application.
2. Set up a public front controller that defines the app root and loads Crystal. For example:

   ```php
   <?php
   declare(strict_types=1);

   define('CRYSTAL_APP_ROOT', dirname(__DIR__));
   define('CRYSTAL_DEBUG', false);

   require_once __DIR__ . '/../Crystal/boot.php';
   ```

   Adjust the paths to match where you place `Crystal/` and your application directory. The web server’s document root should point to your app’s `public/` directory.
3. In your app’s `app.php`, register services and routes using the `$container` and `$router` initialized by the boot file.
4. Configure a database service with your connection settings. Crystal’s database component uses PDO with MySQL; make sure the PHP PDO MySQL driver is enabled.
5. Add controllers under your app’s `Controllers/` directory and views under `Views/`.

The `admin.alpha-code.dev/` directory is an example application and can be used as a starting point, but you can create separate applications or services that share `Crystal/Core/` without adopting the example app's structure. Its `config.php.example` shows the example database configuration shape. Keep real credentials out of version control, and use `CRYSTAL_DEBUG` only for local development.

## Project layout

```text
Crystal/
  boot.php             Framework bootstrap and class loading
  Core/                Container and router
  Components/           Database, query, views, responses, auth, and utilities
  Support/              Reusable helpers
  Tests/                PHPUnit tests
admin.alpha-code.dev/   Example application
assets/                 Shared static assets
```

## Tests

The framework includes PHPUnit tests in `Crystal/Tests/` and a `Crystal/phpunit.xml` configuration. Run them with a compatible PHPUnit installation, for example:

```sh
phpunit -c Crystal/phpunit.xml
```

## License

See [`Crystal/LICENSE`](Crystal/LICENSE).
