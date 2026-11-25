# REMA — Real Estate Management Application

A PHP and MySQL web application where one account can act as a **seller**, **buyer** or **agent** to list, search and transact on properties. Built as a database-systems course project around a normalised relational schema.

![PHP](https://img.shields.io/badge/PHP-8.1-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL%20%2F%20TiDB-schema-4479A1?logo=mysql&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-ready-2496ED?logo=docker&logoColor=white)

---

## Overview

After signing in, a user picks a role for the session:

| Role | What they can do |
|---|---|
| **Seller** | List a property (type, size and unit, ownership status, features, price per unit, total and projected future value, negotiable and brokerage flags, location, usage), edit or delete available listings, review and confirm buyer requests |
| **Buyer** | Search by city, type, usage, price range, size range, negotiability and brokerage; request a property; track purchase history and status |
| **Agent** | Browse a city's available properties and buy several at once, put their own properties up for sale in bulk, and view their agent transaction history. A development-services section is stubbed but not built yet |

### Transaction lifecycle

```
property: available ──buyer/agent request──▶ pending ──seller confirms──▶ sold (for sale) / pending (rent, lease, development)
transaction:                                 pending ──seller confirms──▶ confirmed
```

## Data model

Three InnoDB tables with foreign keys. The full DDL is in [`db.sql`](db.sql).

```
users (user_id PK, username UNIQUE, password, email, contact_info)
   │ 1
   │ n
properties (property_id PK, user_id FK, property_type, property_size, size_unit,
            ownership_status, features, description, estimated_price_per_unit,
            total_value, future_value, seller_type, negotiation, brokering,
            location, city, usage_type, status)
   │ 1
   │ n
transactions (transaction_id PK, property_id FK, buyer_id FK?, agent_id FK?,
              transaction_type, status)
```

## Security

- Every query goes through a small helper (`execute_named_query` in `config.php`) that turns named placeholders into **mysqli prepared statements**.
- Passwords are stored with `password_hash` and checked with `password_verify`.
- All database values are HTML-escaped on output.
- Database credentials come only from environment variables. None are committed.
- TLS with certificate verification is used by default (required by TiDB Cloud).

## Tech stack

| | |
|---|---|
| Backend | PHP 8.1, mysqli |
| Database | MySQL 8 / MariaDB locally, TiDB Cloud in deployment |
| Frontend | Server-rendered HTML and CSS |
| Deployment | Docker image (`php:8.1-apache`). The entrypoint adapts Apache to a platform-provided `$PORT` (for example on Render) |

## Getting started

### Local (PHP + MySQL / MariaDB)

```bash
git clone https://github.com/Rishikesh10925/Dbms_Project.git
cd Dbms_Project

mysql -u root -p -e "CREATE DATABASE rema"
mysql -u root -p rema < db.sql

export DB_HOST=127.0.0.1 DB_PORT=3306 DB_SSL=false \
       DB_USER=root DB_PASS=yourpassword DB_NAME=rema
php -S localhost:8000
```

Open <http://localhost:8000>, register an account and sign in.

### Docker

```bash
docker build -t rema .
docker run -p 8080:80 \
  -e DB_HOST=... -e DB_USER=... -e DB_PASS=... -e DB_NAME=... \
  rema
```

### Configuration

| Variable | Default | Purpose |
|---|---|---|
| `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME` | none | Database connection |
| `DB_PORT` | `4000` | `4000` for TiDB Cloud, `3306` for MySQL |
| `DB_SSL` | `true` | Set to `false` for a local server without TLS |
| `PORT` | none | Apache listen port inside Docker |

## Project structure

```
├── index.php              # login
├── register.php           # sign up
├── dashboard.php          # choose role
├── seller_dashboard.php   # list, manage, delete, view buyer requests
├── edit_property.php
├── confirm_purchase.php   # seller confirms a request
├── buyer_dashboard.php    # search and purchase history
├── buy_property.php       # buyer request
├── agent_dashboard.php    # bulk buy / bulk sell / history
├── agent_buy.php, agent_bulk_sell.php
├── logout.php
├── config.php             # connection + prepared-statement helper
├── db.sql                 # schema
├── styles.css
├── Dockerfile, docker-entrypoint.sh
```

## Limitations

- Role choice is per session rather than stored per account, and pages do not restrict access by role.
- Forms have no CSRF tokens.
- There are no automated tests. The main flow (register → list → search → request → confirm) has been checked by hand against MariaDB.

## Contributors

Rishikesh Talpalikar ([@Rishikesh10925](https://github.com/Rishikesh10925)), Lahari Makkena, Ratnachand Kancharla.
