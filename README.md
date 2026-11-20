# ElleyPAY – Online Banking Management System

ElleyPAY is a web-based banking management system developed with PHP, MySQL, HTML, CSS, JavaScript, and Bootstrap. It provides a simple and user-friendly banking experience, allowing customers to manage their accounts and perform basic banking transactions through an online platform.

The project also includes an administrative dashboard that enables administrators to manage customers, monitor transactions, and oversee banking activities.

## Features

### Customer Features
- User registration and secure login.
- Personal banking dashboard.
- Automatic bank account generation.
- Account balance overview.
- Deposit funds.
- Withdraw funds.
- Transfer funds between registered accounts.
- View transaction history.
- View personal account information.
- Secure logout functionality.

### Administrator Features
- Secure administrator login.
- Administrative dashboard.
- View registered customers.
- Search and manage customer accounts.
- Activate and deactivate customer accounts.
- View individual customer profiles.
- Monitor customer account balances.
- View and monitor all banking transactions.
- Filter transactions by type and status.
- View transaction references and details.

## Technologies Used

| Technology | Purpose |
|---|---|
| PHP | Backend development and application logic |
| MySQL | Database management |
| PDO | Secure database connectivity |
| HTML5 | Web page structure |
| CSS3 | Styling and presentation |
| Bootstrap 5 | Responsive user interface |
| JavaScript | Frontend interactions |
| MAMP | Local development environment |
| phpMyAdmin | Database administration |

## Project Structure

```text
bank-system/
│
├── admin/
│   ├── dashboard.php
│   ├── users.php
│   ├── user-view.php
│   ├── transactions.php
│   └── logout.php
│
├── assets/
│   └── css/
│       └── style.css
│
├── config/
│   └── database.php
│
├── includes/
│   ├── auth.php
│   ├── functions.php
│   ├── header.php
│   └── footer.php
│
├── index.php
├── login.php
├── register.php
├── dashboard.php
├── deposit.php
├── withdraw.php
├── transfer.php
├── transactions.php
├── profile.php
├── forgot-password.php
├── reset-password.php
├── logout.php
└── README.md
```

## Installation and Setup

Follow these steps to run ElleyPAY on your local computer.

### 1. Clone the Repository

Open your terminal and run:

```bash
git clone YOUR_GITHUB_REPOSITORY_URL
```

Navigate into the project directory:

```bash
cd bank-system
```

### 2. Set Up Your Local Server

Install and start MAMP.

Place the project folder inside your MAMP document root, usually:

```text
Applications/MAMP/htdocs/
```

On Windows, use your configured MAMP document root.

### 3. Create the Database

1. Start Apache and MySQL through MAMP.
2. Open phpMyAdmin.
3. Create a database named:

```sql
CREATE DATABASE bank_system
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

4. Import the project's SQL database file if one is included in the repository.

If no database export is included, create the required tables using the project's database schema.

### 4. Configure the Database Connection

Open:

```text
config/database.php
```

Update the connection details to match your local MySQL configuration.

Example:

```php
<?php

$host = "localhost";
$dbname = "bank_system";
$username = "root";
$password = "root";

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

    $pdo->setAttribute(
        PDO::ATTR_EMULATE_PREPARES,
        false
    );

} catch (PDOException $e) {

    die("Database connection failed.");

}
```

### 5. Launch the Application

Open your browser and visit:

```text
http://localhost/bank-system/
```

You can now register a customer account and explore the banking features.

## Security Considerations

ElleyPAY incorporates several security practices:

- Password hashing using PHP's `password_hash()`.
- Password verification using `password_verify()`.
- PDO prepared statements for database queries.
- Session-based authentication.
- Role-based access control for administrative pages.
- Server-side validation of transaction amounts.
- Database transactions for financial operations.

**Important:** This project is an educational banking application and development demonstration. It is not a production-ready financial platform. Real-world deployment requires additional security controls, regulatory compliance, independent security testing, audit logging, and integration with authorized financial institutions and payment providers.

## Future Improvements

Potential improvements include:

- Password reset through email verification.
- Automated email and transaction notifications.
- Two-factor authentication (2FA).
- Improved customer profile management.
- Transaction receipts.
- Enhanced administrative reporting.
- API integration.
- Mobile banking application.
- Production-grade security and regulatory integrations.

## Learning Objectives

This project demonstrates practical implementation of:

- PHP backend development.
- MySQL database design and management.
- CRUD operations.
- Authentication and authorization.
- Secure database interactions using PDO.
- Financial transaction processing.
- Session management.
- Responsive web application development.
- Administrative dashboard development.

## Author

**Elijah Sunday**

Software Developer | Web Developer

GitHub: [Your GitHub Profile](https://github.com/)

