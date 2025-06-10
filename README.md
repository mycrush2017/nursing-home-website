# Nursing Home Website

This is a PHP and MySQL based nursing home website project with an admin dashboard and payment integration (PayPal and Stripe sandbox).

## Features

- Public website with nursing home information and services.
- Admin dashboard with login and company info editing.
- Payment buttons for PayPal and Stripe (sandbox mode).
- Company info stored in MySQL database.
- Simple and modern design using Tailwind CSS.

## Requirements

- PHP 7.4 or higher
- MySQL or MariaDB
- Web server (Apache, Nginx, etc.)

## Setup Instructions

1. Import the database schema:

```bash
mysql -u root -p < database.sql
```

2. Configure database connection in `config.php`:

Update the `$host`, `$user`, `$password`, and `$dbname` variables as needed.

3. Place all project files in your web server's root directory or a subdirectory.

4. Access the public site:

```
http://your-server/index.php
```

5. Access the admin dashboard:

```
http://your-server/admin.php
```

- Default admin credentials:
  - Username: `admin`
  - Password: `admin123`

6. To test payments, use the PayPal sandbox button or the Stripe button (which currently simulates a payment).

## Notes

- Passwords are stored hashed using PHP's `password_hash`.
- For real payment integration, you need to implement Stripe's PHP SDK and PayPal IPN or API.
- This project is a simple demo and should be enhanced for production use.

## License

MIT License
