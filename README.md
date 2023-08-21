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
- [Issues](#issues)

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

9. Set arbitrary values for the PUSHER_ variables in the `.env` file. It does not matter what you set as your `PUSHER_` variables.

    ```bash
    PUSHER_APP_ID=12345
    PUSHER_APP_KEY=qwerty
    PUSHER_APP_SECRET=secret
    PUSHER_HOST=127.0.0.1
    PUSHER_PORT=6001
    PUSHER_SCHEME=http
    PUSHER_APP_CLUSTER=mt1
    ```

    **Note: Ensure the Broadcast Driver is set to pusher in the `.env` file**

    ```bash
    BROADCAST_DRIVER=pusher
    ```

10. Run database migrations to set up the database tables:

   ```bash
   php artisan migrate
   ```

## Usage

### API Endpoints

**Note: Ensure that the `Accept: application/json` header is included in all API requests.**

The Elevator App exposes the following API endpoints:

- `POST /api/register` - Register a new user.
- `POST /api/login` - Authenticate a user.

**Note: The websocket server must be running when hitting the `POST` endpoints or the application will throw a cURL error.**

Authenticated routes:

- `POST /api/create-building` - Create a building with elevators.
- `GET /api/list-buildings` - List buildings with elevators.
- `POST /api/{buildingId}/create-elevator` - Create an elevator for a building.
- `POST /api/call-elevator/{elevatorId}` - Call an elevator.

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
    "name": "Building 1",
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
    "name": "New Elevator 6",
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

3. Start the Elevator Queue Manager to handle Asynchronous Elevator Calls

   ```bash
   php artisan elevator:queue-manager
   ```

4. Start the Queue Worker to handle queues for the other events i.e New Building, New Elevators & Elevator Actions

    ```bash
    php artisan queue:work
    ```

5. Access the app websockets dashboard in your web browser at [http://localhost:8000/app-websockets](http://localhost:8000/app-websockets).

6. Click on the `Connect` button to start displaying dispatched events from the app. Note that events are dispatched when:

- New building is created
- New elevator is created
- Any action an elevator does:
  - Called
  - Moving
  - Stopped
  - Opening Doors
  - Doors Open
  - Doors Closing
  - Doors Closed
  - Idle

Remember to keep the application and WebSockets servers running while using the app.

You can choose to run the Elevator calls synchronously by updating the Queue driver to `sync` from `database`
in the `.env` file and update how the MoveElevator Job is being dispatched from Queue in ``app/Http/Controllers/API/ElevatorController.php`` in callElevator method.

```bash
QUEUE_CONNECTION=sync
```

Change from:

```php
// Dispatch the MoveElevator job to the queue asynchronously
MoveElevator::dispatch($elevatorLog)->onQueue("elevator_{$elevator->id}");
```

To:

```php
MoveElevator::dispatch($elevatorLog);
```

Or:

```php
MoveElevator::dispatchNow($elevatorLog);
```

***Note: The above will cause the call-elevator endpoint to wait until the Job is complete then return a response. For long running Jobs, the endpoint will timeout but the elevator will still be moving***

The above custom commands is to avoid `supervisor` installation incase the team member does not have it installed and configured on local machine.

Ideally using `supervisor` would be the best uproach.

 **Note: All SQL queries executed by the application are logged in ``storage/logs/DB-SQL-queries-executed.log``**

## Issues

If you find a bug, please contact me via email or file an issue on [the issue tracker on GitHub](https://github.com/maina-david/elevator-app/issues).
