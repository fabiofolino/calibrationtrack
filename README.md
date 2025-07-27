# CalibrationTrack

A comprehensive web-based calibration management system built with Laravel and Vue.js for tracking measurement gages, calibration records, and compliance requirements.

![Laravel](https://img.shields.io/badge/Laravel-11.x-red?style=flat-square&logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3.x-green?style=flat-square&logo=vue.js)
![PHP](https://img.shields.io/badge/PHP-8.2+-blue?style=flat-square&logo=php)
![License](https://img.shields.io/badge/License-MIT-yellow?style=flat-square)

## 🎯 Overview

CalibrationTrack is a modern, multi-tenant calibration management system designed for organizations that need to track and manage measurement equipment calibrations. The system provides comprehensive tracking of gages, calibration schedules, measurement data, and compliance reporting.

### Key Features

- **🏢 Multi-Tenant Architecture** - Secure company-based data isolation
- **📏 Gage Management** - Complete lifecycle tracking of measurement equipment
- **📊 Detailed Measurements** - Advanced measurement groups with tolerance tracking
- **📅 Calibration Scheduling** - Automated due date tracking and reminders
- **📈 Compliance Reporting** - Comprehensive audit trails and export capabilities
- **💳 Subscription Management** - Integrated billing with Stripe
- **🔐 Role-Based Access** - Admin and user role management
- **📱 Responsive Design** - Modern, mobile-friendly interface

## 🚀 Quick Start

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL 8.0+ or PostgreSQL 13+
- Redis (optional, for caching and queues)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/calibrationtrack.git
   cd calibrationtrack
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure your `.env` file**
   ```env
   APP_NAME=CalibrationTrack
   APP_URL=http://localhost:8000
   
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=calibrationtrack
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   
   # Stripe Configuration (optional)
   STRIPE_KEY=your_stripe_publishable_key
   STRIPE_SECRET=your_stripe_secret_key
   CASHIER_PRICE_ID=your_stripe_price_id
   
   # Mail Configuration
   MAIL_MAILER=smtp
   MAIL_HOST=your_mail_host
   MAIL_PORT=587
   MAIL_USERNAME=your_mail_username
   MAIL_PASSWORD=your_mail_password
   ```

6. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

7. **Build assets**
   ```bash
   npm run build
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```

Visit `http://localhost:8000` to access the application.

## 🏗️ Architecture

### Technology Stack

- **Backend**: Laravel 11.x with PHP 8.2+
- **Frontend**: Vue.js 3 with Inertia.js
- **Styling**: Tailwind CSS
- **Database**: MySQL/PostgreSQL with Eloquent ORM
- **Authentication**: Laravel Fortify with 2FA support
- **Payments**: Laravel Cashier with Stripe
- **File Storage**: Laravel Storage (supports local, S3, etc.)
- **Testing**: PHPUnit with Feature and Unit tests

### Project Structure

```
app/
├── Http/Controllers/          # Application controllers
├── Models/                    # Eloquent models
├── Policies/                  # Authorization policies
├── Mail/                      # Email templates
├── Exports/                   # Data export classes
└── Console/Commands/          # Artisan commands

resources/
├── js/                        # Vue.js components and pages
├── css/                       # Tailwind CSS styles
└── views/                     # Blade templates

database/
├── migrations/                # Database schema migrations
├── factories/                 # Model factories for testing
└── seeders/                   # Database seeders
```

## 📋 Core Features

### Gage Management
- Complete gage lifecycle tracking
- Department-based organization
- Location and custodian management
- Calibration frequency scheduling
- Checkout/checkin functionality

### Measurement Groups
- Advanced measurement point definition
- Multiple tolerance types (percentage, plus/minus, limits)
- As Found / As Left value tracking
- Automatic pass/fail calculation
- Uncertainty and standards tracking

### Calibration Records
- Dual workflow support (simple and detailed)
- Certificate file attachments
- Automatic due date calculation
- Comprehensive audit logging

### Subscription Management
- Freemium model with gage limits
- Stripe integration for payments
- Trial period support
- Admin subscription management

### Reporting & Exports
- CSV and PDF export capabilities
- Calibration certificates
- Compliance reporting
- Audit trail tracking

## 🔧 Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_NAME` | Application name | CalibrationTrack |
| `APP_ENV` | Environment (local, production) | local |
| `DB_CONNECTION` | Database driver | mysql |
| `STRIPE_KEY` | Stripe publishable key | - |
| `STRIPE_SECRET` | Stripe secret key | - |
| `MAIL_MAILER` | Mail driver | smtp |

### Stripe Configuration

For subscription functionality, configure Stripe in your `.env`:

```env
STRIPE_KEY=pk_test_your_publishable_key
STRIPE_SECRET=sk_test_your_secret_key
CASHIER_PRICE_ID=price_your_price_id
```

## 🧪 Testing

Run the test suite:

```bash
# Run all tests
php artisan test

# Run specific test types
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
```

## 📦 Deployment

### Production Setup

1. **Server Requirements**
   - PHP 8.2+ with required extensions
   - Composer
   - Web server (Apache/Nginx)
   - Database server
   - SSL certificate

2. **Environment Configuration**
   ```bash
   # Set environment to production
   APP_ENV=production
   APP_DEBUG=false
   
   # Configure production database and cache
   # Set up proper mail configuration
   # Configure file storage (S3 recommended)
   ```

3. **Deployment Commands**
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   npm run build
   ```

### Docker Deployment

Use the included `docker-compose.yml`:

```bash
docker-compose up -d
```

## 🔒 Security

- Multi-tenant data isolation
- CSRF protection
- SQL injection prevention
- XSS protection
- Two-factor authentication support
- Role-based access control
- Audit logging

## 📧 Email & Notifications

### Calibration Reminders

Automated email reminders for upcoming calibrations:

```bash
# Send calibration reminders (run daily via cron)
php artisan calibration:reminders

# Dry run to see what emails would be sent
php artisan calibration:reminders --dry-run
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation as needed
- Use semantic commit messages

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Documentation**: Check the inline documentation and comments
- **Issues**: Report bugs via GitHub Issues
- **Email**: contact@yourcompany.com

## 🙏 Acknowledgments

- Built with [Laravel](https://laravel.com)
- Frontend powered by [Vue.js](https://vuejs.org) and [Inertia.js](https://inertiajs.com)
- Styled with [Tailwind CSS](https://tailwindcss.com)
- Payments by [Stripe](https://stripe.com)

---

**CalibrationTrack** - Professional calibration management made simple.
