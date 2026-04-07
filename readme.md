# Foo Trip ✈️

A Symfony 8 web application to manage and browse honeymoon destinations.

---

## Features

- Public destination listing with images and descriptions
- Destination detail page
- Admin backoffice to create, edit and delete destinations (requires login)
- Public REST API with optional name filter
- CLI command to export destinations to CSV via the API
- Database fixtures for destinations and users

---

## Requirements

- PHP 8.4+
- Composer
- Symfony CLI (optional, for the built-in server)

---

## Installation

```bash
git clone git@github.com:Mozoou/foo-trip.git
cd foo-trip
composer install
```

---

## Database setup

The app uses **SQLite** — no external database server needed.

```bash
# Create the schema
php bin/console doctrine:schema:create

# Load fixtures (destinations + users)
php bin/console doctrine:fixtures:load
```

---

## Running the app

```bash
symfony serve
# or
php -S localhost:8000 -t public/
```

Then open [http://localhost:8000](http://localhost:8000).

---

## Test accounts (loaded by fixtures)

| Role     | Email                    | Password      |
|----------|--------------------------|---------------|
| Admin    | admin@foo-trip.com       | admin1234     |
| Customer | customer@foo-trip.com    | customer1234  |

**Admin** can log in at `/login` and access the backoffice at `/admin/destinations`.

**Customer** can browse public pages and the API but cannot access `/admin`.

---

## REST API

All endpoints are public (no authentication required).

| Method | Endpoint                  | Description                        |
|--------|---------------------------|------------------------------------|
| GET    | `/api/destinations`       | List all destinations              |
| GET    | `/api/destinations?name=X`| Filter by name (partial match)     |
| GET    | `/api/destinations/{id}`  | Get a single destination           |

Example:
```bash
curl http://localhost:8000/api/destinations
curl http://localhost:8000/api/destinations?name=Paris
```

---

## Export command

Fetches all destinations from the API and exports them to a CSV file.

```bash
php bin/console app:export-destinations
# or specify a custom output path
php bin/console app:export-destinations /tmp/destinations.csv
```

The server must be running when the command is executed (it calls the API over HTTP).

Example output (`destinations.csv`):

| name  | description                        | price | duration |
|-------|------------------------------------|-------|----------|
| Paris | 3 nights in a hotel                | 100   | 7 days   |
| Tunis | 10 nights in a villa               | 200   | 17 days  |

---

## Running tests

```bash
php bin/phpunit
```

The test suite uses a separate SQLite database (`var/data_test.db`) and covers:

- Public pages (home, destination detail)
- REST API (list, filter, single item, 404)
- Export command (CSV content, headers, error handling)
- Fixtures integrity (destinations and users)
- Security (admin access, customer access, login/logout, failed auth)

---

## Required features

- Create a "Destination" entity with the following fields: name, description, price, duration of the trip, image of the destination. These fields should not be nullable
- Set up a database to store destinations
- Create a home page that displays the list of destination destinations honeymoon with their images and descriptions. Each destination should have a link so the user can click and access to the details of the destination
- Allow users to click on a destination to view the full details of that destination.
- Implement a basic authentication system for administrators.
- Add a backoffice where administrators can create, update and delete destinations.
- Add validation rule on the destination creation
- Expose a REST API for Destination, the API should be public that means no security token is needed
- We can filter the API per destination and name
- Create a Symfony command that GET all destinations via the API and export them into a CSV like the example below
- Add tests and tooling

Contact me at `contact[at]smaine.me` in case of question.
