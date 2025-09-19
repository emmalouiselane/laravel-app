# Common

## Features

- **User Authentication**: Secure user accounts and sessions
- **Responsive Design**: Works on desktop and mobile devices


# Task Management Application

A comprehensive task management system built with Laravel, featuring one-time tasks, recurring tasks, and habit tracking with progress monitoring.

## Features

- **Task Management**: Create, read, update, and delete tasks
- **Task Types**:
  - One-time tasks
  - Recurring tasks with custom frequency
  - Habit tracking with progress monitoring
- **Daily Planner**: View and manage tasks by date


# Budget Management Application

A comprehensive budget management system built with Laravel, featuring monthly budgeting with direct debit tracking.

## Features

- **Budget Tracking**: Create, read, update, and delete payments / budgets
- **Budget Types**:
  - Monthly budgets with direct debit tracking
- **Monthly Budget**: View and manage budgets by month


# Development Notes

## Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js & NPM
- MySQL 5.7+ or equivalent database

## Installation

1. Clone the repository:
   ```bash
   git clone [your-repository-url]
   cd laravel-app
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install NPM dependencies:
   ```bash
   npm install
   ```

4. Create a copy of the .env file:
   ```bash
   cp .env.example .env
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Configure your database in the `.env` file:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=laravel
   DB_USERNAME=root
   DB_PASSWORD=
   ```

7. Run database migrations:
   ```bash
   php artisan migrate
   ```

8. Build assets:
   ```bash
   npm run build
   ```

## Running the Application

1. Start the development server:
   ```bash
   php artisan serve
   ```

2. In a new terminal, start Vite for hot module replacement:
   ```bash
   npm run dev
   ```

3. Open your browser to:
   ```
   http://localhost:8000
   ```

## Usage

1. Register a new account or log in with existing credentials
2. Navigate to the dashboard to view your tasks
3. Use the date picker to view tasks for specific dates
4. Create new tasks by clicking the "Add Task" button
5. Mark tasks as complete or delete them as needed

## Development

- Run tests:
  ```bash
  php artisan test
  ```

- Run code style fixer:
  ```bash
  ./vendor/bin/pint
  ```

## License

This project is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
