    1 # Project Overview
    2
    3 This is a Laravel-based API application,
      designed to serve as the backend for a
      visitor management system. It leverages the
      Laravel Framework for robust backend
      capabilities, with Laravel Sanctum handling
      API authentication. The project follows a
      feature-driven architecture, organizing
      related functionalities like `Auth`,
      `Location`, and `User` into distinct modules,
      each with its own routes, controllers,
      repositories, requests, and services. While
      primarily a backend API, it integrates with
      Vite for front-end asset compilation.
    4
    5 # Building and Running
    6
    7 This project uses Composer for PHP dependency
      management and NPM for JavaScript
      dependencies.
    8
    9 ## Setup

10
1
2 This command performs the following actions:
3 _ Installs PHP dependencies via Composer.
4 _ Copies `.env.example` to `.env` if it
doesn't exist.
5 _ Generates an application key.
6 _ Runs database migrations.
7 _ Installs JavaScript dependencies via npm
(ignoring scripts).
8 _ Builds frontend assets using Vite.
9
10 ## Development
11
12 To run the application in development mode
with hot-reloading for frontend assets, a
local PHP server, queue listener, and logs
tailing, use:
composer run dev

    1
    2 This command runs the following concurrently:
    3 *   `php artisan serve`
    4 *   `php artisan queue:listen --tries=1
      --timeout=0`
    5 *   `php artisan pail --timeout=0` (for
      real-time logs)
    6 *   `npm run dev` (for Vite development
      server)
    7
    8 ## Building Frontend Assets
    9

10 To compile frontend assets for production:
npm run build
1
2 ## Running Tests
3
4 To execute the project's PHPUnit tests:
composer run test

    1
    2 This command first clears the configuration
      cache and then runs all unit and feature
      tests.
    3
    4 # Development Conventions
    5
    6 *   **Framework:** Laravel (version 13.x).
    7 *   **API Authentication:** Laravel Sanctum
      is used for secure API authentication.
    8 *   **Code Structuring:** A feature-driven
      approach is evident, with features (e.g.,
      `Auth`, `Location`, `user`) encapsulated
      within `app/Features/` directories.
    9 *   **API Responses:** A consistent JSON
      response structure is enforced using the
      `App\Traits\ApiResponse` trait, providing
      `successResponse` and `errorResponse`
      methods.

10 _ **Database:** Database schema is managed
through Laravel migrations. During testing,
an in-memory SQLite database is utilized
(`DB_CONNECTION=sqlite`,
`DB_DATABASE=:memory:` in `phpunit.xml`).
11 _ **Code Style:** The `laravel/pint`
package is included for enforcing consistent
PHP coding standards.
12 _ **Testing:** PHPUnit is the chosen
framework for both unit and feature testing.
13 _ **Frontend Tooling:** Vite is used for
frontend asset bundling, and TailwindCSS is
included as a development dependency for
styling.
