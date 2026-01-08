If you love what I do or have any requests of what I should do next, please consider supporting me on Ko-fi!
[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/H2H11F73H5)

# Laravel Productivity Suite

A productivity application built with Laravel, featuring task management, budget tracking, and sticky notes with Google OAuth authentication.

## 🌐 Live Demo

**Check out the live application**: [https://laravel-production-7569.up.railway.app/](https://laravel-production-7569.up.railway.app/)

## ✨ Features

### 🔐 Authentication
- **Google OAuth Integration**: Secure authentication with Google accounts
- **User Management**: User-specific data and preferences
- **Session Management**: Secure session handling

### 📋 Task Management Application
A comprehensive task management system featuring:
- **Task Types**:
  - One-time tasks
  - Recurring tasks with custom frequency
  - Habit tracking with progress monitoring
- **Daily Planner**: View and manage tasks by date
- **Task Operations**: Create, read, update, and delete tasks
- **Progress Tracking**: Monitor habit completion and task status

### 💰 Budget Management Application
A comprehensive budget management system featuring:
- **Budget Tracking**: Create, read, update, and delete payments/budgets
- **Recurring Payments**: Repeatable payments with frequency options (weekly, monthly, yearly)
- **Occurrence Tracking**: Each recurrence generated as a Payment Occurrence, marked as paid individually
- **Pay Period Modes**: Weekly or Monthly pay periods with configurable start days
- **Financial Overview**: Totals for incoming/outgoing/net/remaining amounts
- **Smart Ordering**: Occurrences ordered by date, direction, and amount

### 📝 Sticky Notes Application
A feature-rich sticky notes system with:
- **Drag & Drop**: Move sticky notes around the board with mouse or touch
- **Resizable**: Resize notes by dragging the corner handle
- **Mobile Support**: Full touch support for mobile devices
- **Auto-Resize**: Notes automatically resize to fit content
- **Color Options**: 8 predefined colors for visual organization
- **User-Specific**: Each user has their own private sticky notes board
- **Inline Editing**: Double-click to edit title and content directly
- **Smart Positioning**: Mobile-friendly positioning within screen bounds

## 🛠 Tech Stack

- **Backend**: Laravel 12.0, PHP 8.2+
- **Frontend**: BladeWind UI Components, TailwindCSS
- **Database**: PostgreSQL
- **Authentication**: Google OAuth 2.0
- **Build Tool**: Vite with Laravel Vite Plugin
- **Deployment**: Railway

## 📁 Project Structure

```
laravel-app/
├── app/
│   ├── Http/Controllers/     # Application controllers
│   ├── Models/              # Eloquent models
│   └── Middleware/          # Custom middleware
├── resources/
│   ├── views/              # Blade templates
│   │   ├── layouts/        # Layout templates
│   │   └── components/      # View components
│   └── css/                # Stylesheets
├── routes/
│   ├── web.php             # Web routes
│   └── api.php             # API routes
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/           # Database seeders
└── config/                # Configuration files
```


## 🚀 Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js & NPM
- PostgreSQL database
- Google OAuth credentials (for authentication)

### Installation

1. **Clone the repository**:
   ```bash
   git clone [your-repository-url]
   cd laravel-app
   ```

2. **Install PHP dependencies**:
   ```bash
   composer install
   ```

3. **Install NPM dependencies**:
   ```bash
   npm install
   ```

4. **Environment setup**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure your `.env` file**:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   
   # Google OAuth
   GOOGLE_CLIENT_ID=your_google_client_id
   GOOGLE_CLIENT_SECRET=your_google_client_secret
   GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
   ```

6. **Run database migrations**:
   ```bash
   php artisan migrate
   ```

7. **Build assets**:
   ```bash
   npm run build
   ```

### Running the Application

1. **Start the development server**:
   ```bash
   php artisan serve
   ```

2. **Start Vite for hot module replacement** (in a new terminal):
   ```bash
   npm run dev
   ```

3. **Access the application**:
   - Main app: http://localhost:8000
   - Live demo: https://laravel-production-7569.up.railway.app/

## 📖 Usage Guide

### 🔑 Authentication
- Click "Continue with Google" to sign in with your Google account
- Each user gets their own private data space

### 📋 Planner (Task Management)
1. Navigate to the Planner page from the dashboard
2. Use the date picker to view tasks for specific dates
3. Create new tasks by clicking the "Add Task" button:
   - Choose task type: one-time, recurring, or habit
   - For recurring tasks, set frequency and optional end date
   - For habits, set target count and whether skippable
4. Mark tasks as complete or delete them as needed
5. Habits track progress toward their target count per day

### 💰 Budget Management
1. Navigate to the Budget page from the dashboard
2. Use the header to switch pay periods (Current, previous/next)
3. Add a payment using the overlay form:
   - Check "Repeatable" to enable recurring fields
   - Choose Frequency (weekly, monthly, yearly) and optional Repeat End Date
4. Review generated occurrences within the selected pay period:
   - Each occurrence can be marked as paid individually
5. View totals for Incoming, Outgoing, Net Leftover, and Remaining Unpaid
6. Open Settings to choose pay period mode (weekly or monthly) and configure start day

### 📝 Sticky Notes
1. Navigate to the Sticky Notes page from the dashboard
2. Click "Add Sticky" button to create a new note:
   - Enter title and content
   - Choose a color from the 8 available options
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

## 🛠 Development

### Running Tests
```bash
php artisan test
```

### Code Style
```bash
./vendor/bin/pint
```

### Error Reporting
The application includes automated error reporting that creates GitHub issues when errors occur. Configure in `.env`:
```env
GITHUB_REPORTING_ENABLED=true
GITHUB_TOKEN=your_github_token
GITHUB_REPO=your_username/your_repo
```

## 🚀 Deployment

The application is deployed on Railway. To deploy your own instance:

1. Connect your repository to Railway
2. Configure environment variables in Railway dashboard
3. Railway will automatically build and deploy your application

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This project is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
