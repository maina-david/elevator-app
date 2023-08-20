# Elevator App

This document provides instructions for setting up, configuring, and using the Elevator App on your local machine.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Usage](#usage)
  - [API Endpoints](#api-endpoints)
  - [Request Payloads](#request-payloads)
- [Running Tests](#running-tests)
- [Starting the App](#starting-the-app)

## Prerequisites

Before you begin, ensure that you have the following installed on your system:

- [Composer](https://getcomposer.org/)
- PHP version 8.1 or later

## Installation

1. Clone or download the repository to your local machine.

2. Navigate to the project directory in your terminal.

3. Make sure you have the required PHP version by running:

   ```bash
   php -v
   ```

   If the version is not 8.1 or later, please install the required version.

4. Install project dependencies using Composer:

   ```bash
   composer install
   ```

5. Copy the `.env.example` file and create a `.env` file:

   ```bash
   cp .env.example .env
   ```

6. Edit the `.env` file to provide your database credentials.

7. Laravel will attempt to create the specified database if it doesn't exist. Don't worry if the database isn't created manually.

8. Generate the application key:

   ```bash
   php artisan key:generate
   ```

9. Run database migrations to set up the database tables:

   ```bash
   php artisan migrate
   ```

## Usage

### API Endpoints

The Laravel Elevator App exposes the following API endpoints:

- `POST /register` - Register a new user.
- `POST /login` - Authenticate a user.

Authenticated routes (require the Accept: application/json header):

- `POST /create-building` - Create a building with elevators.
- `GET /list-buildings` - List buildings with elevators.
- `POST /{building}/create-elevator` - Create an elevator for a building.
- `POST /call-elevator/{elevator}` - Call an elevator.

### Request Payloads

- Payload for registering a new user:

  ```json
  {
    "name": "Test User",
    "email": "test@test.com",
    "password": "password",
    "password_confirmation": "password"
  }
  ```

- Payload for logging in:

  ```json
  {
    "email": "test@test.com",
    "password": "password"
  }
  ```

- Payload for creating a building:

  ```json
  {
    "name": "Building 4",
    "number_of_floors": 10,
    "elevators": [
      { "name": "Elevator 1" },
      { "name": "Elevator 2" },
      { "name": "Elevator 3" },
      { "name": "Elevator 4" },
      { "name": "Elevator 5" }
    ]
  }
  ```

- Payload for creating an elevator:

  ```json
  {
    "name": "New Elevator 2",
    "active": true
  }
  ```

- Payload for calling an elevator:

  ```json
  {
    "target_floor": 5
  }
  ```

## Running Tests

To run tests, execute the following command:

```bash
php artisan test
```

Tests will run on a clean database and will refresh it after completion.

## Starting the App

1. Start the Laravel app:

   ```bash
   php artisan serve
   ```

2. Start the WebSockets server:

   ```bash
   php artisan websockets:serve
   ```

3. Access the app in your web browser at [http://localhost:8000](http://localhost:8000) or [http://localhost:8000/app-websockets](http://localhost:8000/app-websockets) if not redirected.

Remember to keep the application and WebSockets servers running while using the app.

For any questions or issues, please contact me via email.
