If you love what I do or have any requests of what I should do next, please consider supporting me on Ko-fi!
[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/H2H11F73H5)

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

A comprehensive budget management system built with Laravel, featuring budgeting with direct debit tracking and flexible pay periods.

## Features

- **Budget Tracking**: Create, read, update, and delete payments/budgets
- **Recurring Payments**: Repeatable payments with frequency options (weekly, monthly, yearly)
- **Occurrence Tracking**: Each recurrence is generated as a Payment Occurrence and can be marked as paid individually
- **Pay Period Modes**: Weekly or Monthly pay periods
  - Monthly supports a configurable start day (e.g., 28th)
- **Responsive UI/UX**: Overlay add/edit forms, mobile-friendly layout, accent-themed buttons
- **Totals & Ordering**: Totals for incoming/outgoing/net/remaining, occurrences ordered by date, then direction, then amount (desc)


# Sticky Notes Application

A comprehensive sticky notes system built with Laravel, featuring drag-and-drop functionality, mobile support, and user-specific boards.

## Features

- **Sticky Notes Management**: Create, read, update, and delete sticky notes
- **Drag & Drop**: Move sticky notes around the board with mouse or touch
- **Resizable**: Resize sticky notes by dragging the corner handle
- **Mobile Support**: Full touch support for mobile devices (drag, resize, edit)
- **Auto-Resize**: Sticky notes automatically resize to fit their content
- **Colour Options**: 8 predefined colours for visual organization
- **User-Specific**: Each user has their own private sticky notes board
- **Inline Editing**: Double-click to edit title and content directly
- **Responsive Design**: Works seamlessly on desktop and mobile devices
- **Smart Positioning**: Mobile-friendly positioning keeps notes within screen bounds


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

### Planner (Todos)

1. Navigate to the Planner page from the dashboard
2. Use the date picker to view tasks for specific dates
3. Create new tasks by clicking the "Add Task" button
   - Choose task type: one-time, recurring, or habit
   - For recurring tasks, set frequency and optional end date
   - For habits, set target count and whether skippable
4. Mark tasks as complete or delete them as needed
5. Habits track progress toward their target count per day

### Budget

1. Navigate to the Budget page from the dashboard
2. Use the header to switch pay periods (Current, previous/next)
3. Add a payment using the overlay form
   - Check "Repeatable" to enable recurring fields
   - Choose Frequency (weekly, monthly, yearly) and optional Repeat End Date
4. Review generated occurrences within the selected pay period
   - Each occurrence can be marked as paid individually
5. Totals show Incoming, Outgoing, Net Leftover, and Remaining Unpaid
6. Open Settings to choose pay period mode (weekly or monthly) and, for monthly, select a start day

### Sticky Notes

1. Navigate to the Sticky Notes page from the dashboard
2. Click "Add Sticky" button to create a new note
   - Enter title and content
   - Choose a colour from the 8 available options
   - Click "Add Note" to save
3. **Desktop Usage**:
   - Drag notes by holding and moving the mouse
   - Resize by dragging the bottom-right corner
   - Hover to see edit/delete buttons
   - Double-click title/content to edit inline
4. **Mobile Usage**:
   - Touch and hold to drag notes
   - Touch the resize handle to adjust size
   - Edit/delete buttons always visible (60% opacity)
   - Double-tap to edit inline
5. **Advanced Features**:
   - Notes auto-resize to fit their content
   - All changes saved automatically
   - Smart positioning keeps notes within screen bounds
   - Each user has their own private board

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
