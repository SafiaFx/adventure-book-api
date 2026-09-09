# Adventure Book API

A small, read-only API for exploring books and their authors, built with **PHP, Slim 4 and PDO**. It demonstrates routing, dependency injection, relational database queries, input validation and automated endpoint testing.

Originally developed as part of the **IO Academy Full Stack Track course**, then improved with assisted refactoring, reproducible setup, error handling and tests. The original project used MySQL; this version also supports SQLite for a quick local demo.

## Technologies used

| Technology | How it is used in this project |
| --- | --- |
| **PHP 8.2+** | Implements the API logic, validates incoming IDs and prepares responses. |
| **Slim 4** | Routes HTTP requests to controllers and handles errors through middleware. |
| **PHP-DI** | Supplies controllers with their model and database dependencies, keeping setup separate from request handling. |
| **PDO** | Connects PHP to the database and executes SQL. Queries involving IDs use prepared statements with bound parameters. |
| **MySQL** | Stores books and authors in related tables when running with Docker. SQL joins retrieve books by author. |
| **SQLite** | Provides a simple local demo database without a separate database server, plus isolated databases for automated tests. |
| **HTTP and JSON** | Let clients request catalogue data through GET endpoints and receive structured data with appropriate HTTP status codes. |
| **Composer** | Installs PHP dependencies, configures class autoloading and provides commands to start the API, set up SQLite and run tests. |
| **Docker and Docker Compose** | Package the PHP application and run it alongside MySQL with database initialization and readiness checks. |
| **PHPUnit** | Tests endpoints, database queries, validation and error responses. |
| **Python** | Runs a small HTTP smoke test against the running API to check the complete setup. |
| **GitHub Actions** | Automatically runs the PHP test suite and Docker/MySQL smoke checks on pushes and pull requests. |

A request such as `GET /books/3` is routed by Slim to a controller. The controller validates the ID and asks the model to query the database through PDO, then returns the book as JSON. This separates HTTP handling from database access.

## Quick start: SQLite

Requirements: PHP 8.2 or later, Composer 2, and the `pdo_sqlite` extension. The test suite also needs the usual PHPUnit extensions, including DOM, XML and mbstring. Composer checks these during installation.

From the repository directory:

```bash
composer install
composer db:setup
composer start
```

Open <http://localhost:8080/books>. Setup creates a local SQLite database with three authors and four books. Running setup again leaves existing data unchanged.

```bash
curl http://localhost:8080/books/3
```

```json
{"id":3,"book":"Treasure Island","author_id":2}
```

## Run with Docker and MySQL

Requirements: Docker Desktop with its engine running, or Docker Engine with Compose.

```bash
cp .env.example .env
docker compose up --build -d
```

Open <http://localhost:8080/books>. Compose starts MySQL, creates the tables, seeds the catalogue, waits for the database to be ready, then starts the API. PHP and Composer do not need to be installed on your host.

```bash
docker compose logs api
docker compose down
```

The database persists in a Docker volume. Initialization scripts run only when the database volume is first created. The example passwords are for local development only. The API binds to localhost and the database has no host port exposed.

## Endpoints

| Method | Path | Result |
| --- | --- | --- |
| GET | `/authors` | All authors |
| GET | `/books` | All books |
| GET | `/books/{id}` | One book object |
| GET | `/authors/{id}/books` | Books belonging to an author |
| GET | `/` | Original alias for `/authors` |
| GET | `/{id}/books` | Original alias for `/authors/{id}/books` |

IDs must be positive integers without leading zeroes. All responses use `application/json; charset=utf-8`. Lists are ordered by ID. An existing author with no books returns `[]`.

Errors use HTTP status codes and a consistent body:

```json
{"error":{"status":404,"message":"Book not found."}}
```

- `400`: invalid ID.
- `404`: book, author or route not found.
- `405`: unsupported HTTP method; the `Allow` header lists supported methods.
- `500`: unexpected server or database failure. Details are logged on the server and are not sent to clients.

**Compatibility note:** the original `/books/{id}` returned a one-item array. It now returns a single object; missing records return 404. The original URLs remain available.

## Configuration

The PHP application reads environment variables directly. It does **not** automatically load `.env`; Docker Compose reads that file and passes the database settings to the API container.

| Variable | Default | Purpose |
| --- | --- | --- |
| `DB_DRIVER` | `sqlite` | `sqlite` or `mysql` |
| `DB_PATH` | `var/books.sqlite` in the project | SQLite database path |
| `DB_HOST` | `127.0.0.1` | MySQL host; Compose sets `db` |
| `DB_PORT` | `3306` | MySQL port |
| `DB_NAME` | `book_app` | MySQL database |
| `DB_USER` | `book_api` | MySQL user |
| `DB_PASSWORD` | Empty | MySQL password |
| `DB_ROOT_PASSWORD` | No default | Compose database initialization only |
| `APP_PORT` | `8080` | Compose host port |

For an existing MySQL server, create a database and user, run `database/01-schema.mysql.sql` and then `database/02-seed.sql` against the empty database, and export the appropriate `DB_*` variables before `composer start`. Install `pdo_mysql` in PHP. The seed SQL is intended for an empty database, not repeated imports.

## Tests

```bash
composer test
composer validate --strict
```

The endpoint tests use a fresh SQLite database in memory for every test. They exercise routing, controllers and real SQL queries together, including filtering, aliases, invalid IDs, absent records, empty results, unsupported methods and safe error responses. They do not touch the local demo database. The deliberate database-failure test writes an expected error to the server log.

GitHub Actions runs the suite on PHP 8.2, 8.3 and 8.4. SQLite tests alone do not establish MySQL compatibility; the separate Docker smoke job starts the full MySQL setup and checks its HTTP responses.

## Project structure

```text
app/                  Application bootstrap and routes
src/Controllers/      Request handlers
src/Models/           PDO queries for books and authors
src/Factories/        Database connection configuration
src/Http/             JSON response and ID validation helpers
public/               HTTP entry point and development router
bin/                  Local database setup command
database/             MySQL/SQLite schemas and sample catalogue
tests/                Automated endpoint tests
```

## Scope and next steps

This is a learning and portfolio project. It provides a small public catalogue, without accounts, editing, search or pagination. The built-in PHP server and Compose configuration are for local development; a public deployment would need a production web server and its own operational configuration.

Possible next additions are search, pagination and authenticated catalogue editing. The current focus is making the original read-only API understandable, runnable and tested.

## Credits

Based on the [IO Academy Slim 4 skeleton](https://github.com/Mayden-Academy/slim4-skeleton) and [Slim Framework](https://www.slimframework.com/). The starter's Git history is retained. The catalogue uses book titles and author names only; no book text is included.
