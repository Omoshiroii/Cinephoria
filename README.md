# Cinephoria - Setup & Troubleshooting Guide

## Overview
Cinephoria is a Letterboxd-like film review and tracking application built with PHP, MySQL, and modern CSS.

## Fixed Issues

This project had the following issues that have been corrected:

### 1. **Missing Database Helper Functions** ✓
- **Problem**: `includes/db.php` was empty, missing database connection and query functions
- **Solution**: Implemented complete DB helper with `db_select()`, `db_select_one()`, `db_execute()`, `db_count()`

### 2. **Missing Authentication & Utility Functions** ✓
- **Problem**: `includes/functions.php` was empty, missing login/password functions
- **Solution**: Implemented password hashing, validation, session management, and utility functions

### 3. **Header/Footer Structure** ✓
- **Problem**: Stray Markdown code fences in include files causing syntax errors
- **Solution**: Cleaned up all include files to proper PHP-only syntax

### 4. **No Database Setup** ✓
- **Problem**: Database schema didn't exist
- **Solution**: Created `setup_database.php` script to auto-initialize database structure

## Quick Start

### 1. Verify PHP Environment
- Ensure you have PHP 7.4+ installed
- MySQL/MariaDB running on `localhost`

### 2. Configure Database Credentials
Edit `includes/config.php` if needed:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cinephoria_db');
define('DB_USER', 'root');
define('DB_PASS', '');  // Change if you have a root password
```

### 3. Initialize the Database
- Database `cinephoria_db` is created
- Tables `users`, `movies`, `user_ratings`, `watchlist` are created
- Sample movies and a test user are added to the database

### 4. Test Login
Default test account:
- **Email**: `test@cinephoria.com`
- **Password**: `Test123`

## Features

- ✓ User registration & authentication
- ✓ Browse movies catalog
- ✓ Rate and review movies
- ✓ Manage watchlist
- ✓ User profiles with stats
- ✓ Search functionality
- ✓ Responsive design

## File Structure

```
/
├── index.php              # Homepage with featured movies
├── login.php              # Login page
├── register.php           # Registration page
├── movies.php             # Movie list & search
├── movie-detail.php       # Individual movie page
├── profile.php            # User profile
├── logout.php             # Logout handler
├── setup_database.php     # Database initialization
├── includes/
│   ├── config.php         # Configuration & basic utility functions
│   ├── db.php             # Database helper functions
│   ├── functions.php      # Authentication & utility functions
│   ├── header.php         # Page header template
│   └── footer.php         # Page footer template
└── css/
    └── style.css          # Styling
```

## Database Schema

### Users Table
```
id, username, email, password, created_at, updated_at
```

### Movies Table
```
id, title, description, release_year, poster_url, rating, genre, created_at
```

### User Ratings Table
```
id, user_id, movie_id, rating, review, created_at
```

### Watchlist Table
```
id, user_id, movie_id, added_at
```

## Common Fixes Applied

1. **Added Database Connection Pool**
   - `get_db_connection()` - Singleton pattern for DB
   - `close_db_connection()` - Safe cleanup

2. **Added Security Functions**
   - `clean()` - XSS prevention via htmlspecialchars
   - `hash_password()` - Bcrypt password hashing
   - `verify_password()` - Secure password verification

3. **Added Session Management**
   - `login_user()` - Create session after login
   - `logout_user()` - Destroy session safely
   - `require_login()` - Redirect to login if not authenticated

4. **Added Validation Functions**
   - `is_valid_email()` - Email format validation
   - `validate_password_strength()` - Password complexity check

## Troubleshooting

### "Database connection failed"
- Check MySQL is running: `services.msc` → MySQL should be running
- Verify credentials in `includes/config.php`
- Run `setup_database.php` again

### "Call to undefined function..."
- Ensure all `require_once` statements in PHP files reference correct paths
- Check file permissions: includes folder must be readable

### "No movies displayed"
- Run `setup_database.php` to populate sample data
- Check `movies.php` page loads without errors

### "Login not working"
- Verify database was created: `setup_database.php`
- Check test user exists in database
- Look for PHP errors in browser console (F12)

## Development Notes

- All passwords use PHP's `password_hash()` with BCRYPT
- Database uses UTF-8 MB4 for full Unicode support
- Session lifetime set to 24 hours in config
- Debug mode is ON (set `DEBUG_MODE` to `false` in production)

## Security Recommendations

For production use:

1. Set `DEBUG_MODE` = `false` in `includes/config.php`
2. Use a strong database password
3. Hash all user inputs with `clean()` function
4. Enable HTTPS
5. Use environment variables for credentials (not hardcoded)
6. Add CSRF tokens to forms
7. Implement rate limiting on login attempts
8. Add file upload validation (if adding image uploads)

## Future Improvements

- [ ] Add email verification for registration
- [ ] Implement password reset
- [ ] Add pagination to all lists
- [ ] Add movie poster image uploads
- [ ] Add user profile editing
- [ ] Add social features (follow users, likes)
- [ ] Add API endpoints
- [ ] Add dark/light theme toggle
- [ ] Implement movie recommendations
- [ ] Add caching layer

---