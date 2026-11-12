<?php

require_once "../includes/auth.php";
require_once "../config/database.php";
require_once "../includes/functions.php";

// Admin only
if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
    redirect("../dashboard.php");
}

// Get user ID
$userId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($userId <= 0) {
    redirect("users.php");
}

// Get user + account information
$stmt = $pdo->prepare("
    SELECT
        u.id,
        u.first_name,
        u.last_name,
        u.email,
        u.phone,
        u.role,
        u.status,
        u.created_at,

        a.id AS account_id,
        a.account_number,
        a.account_type,
        a.balance,
        a.currency,
        a.account_status

    FROM users u

    LEFT JOIN accounts a
        ON a.user_id = u.id

    WHERE u.id = ?

    LIMIT 1
");

$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    redirect("users.php");
}

// Get transaction statistics
$statsStmt = $pdo->prepare("
    SELECT
        COUNT(*) AS total_transactions,

        COALESCE(
            SUM(
                CASE
                    WHEN transaction_type = 'deposit'
                    AND status = 'completed'
                    THEN amount
                    ELSE 0
                END
            ),
            0
        ) AS total_deposits,

        COALESCE(
            SUM(
                CASE
                    WHEN transaction_type = 'withdrawal'
                    AND status = 'completed'
                    THEN amount
                    ELSE 0
                END
            ),
            0
        ) AS total_withdrawals,

        COALESCE(
            SUM(
                CASE
                    WHEN transaction_type = 'transfer'
                    AND status = 'completed'
                    THEN amount
                    ELSE 0
                END
            ),
            0
        ) AS total_transfers

    FROM transactions

    WHERE account_id = ?
");

$statsStmt->execute([
    $user["account_id"] ?? 0
]);

$stats = $statsStmt->fetch();

// Get recent transactions
$transactionStmt = $pdo->prepare("
    SELECT
        id,
        transaction_reference,
        transaction_type,
        amount,
        status,
        description,
        created_at

    FROM transactions

    WHERE account_id = ?

    ORDER BY created_at DESC

    LIMIT 20
");

$transactionStmt->execute([
    $user["account_id"] ?? 0
]);

$transactions = $transactionStmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        User Details - <?php echo e($user["first_name"] . " " . $user["last_name"]); ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background: #f5f7fb;
        }

        .admin-navbar {
            background: #111827;
        }

        .admin-navbar .navbar-brand,
        .admin-navbar .nav-link {
            color: #ffffff;
        }

        .admin-navbar .nav-link:hover {
            color: #d1d5db;
        }

        .page-header {
            margin-top: 35px;
            margin-bottom: 25px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.06);
        }

        .stat-card {
            min-height: 130px;
        }

        .stat-title {
            color: #6b7280;
            font-size: 14px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
        }

        .profile-label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 3px;
        }

        .profile-value {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 18px;
        }

        .badge {
            font-size: 12px;
        }

        .table th {
            white-space: nowrap;
        }

    </style>

</head>

<body>

<!-- Admin Navbar -->

<nav class="navbar navbar-expand-lg admin-navbar">

    <div class="container">

        <a class="navbar-brand fw-bold" href="dashboard.php">
            ELLEY-PAY Admin
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#adminNavbar"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div
            class="collapse navbar-collapse"
            id="adminNavbar"
        >

            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="users.php">
                        Users
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="transactions.php">
                        Transactions
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="../dashboard.php">
                        Customer Area
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php">
                        Logout
                    </a>
                </li>

            </ul>

        </div>

    </div>

</nav>


<div class="container">

    <!-- Page Header -->

    <div class="page-header">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h2 class="fw-bold mb-1">
                    User Details
                </h2>

                <p class="text-muted mb-0">
                    View customer account information and transaction history.
                </p>

            </div>

            <a
                href="users.php"
                class="btn btn-secondary"
            >
                ← Back to Users
            </a>

        </div>

    </div>


    <!-- User Information -->

    <div class="card mb-4">

        <div class="card-header bg-white py-3">

            <h5 class="mb-0 fw-bold">
                Customer Information
            </h5>

        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-4">

                    <div class="profile-label">
                        Full Name
                    </div>

                    <div class="profile-value">
                        <?php
                        echo e(
                            $user["first_name"] . " " .
                            $user["last_name"]
                        );
                        ?>
                    </div>

                </div>


                <div class="col-md-4">

                    <div class="profile-label">
                        Email Address
                    </div>

                    <div class="profile-value">
                        <?php echo e($user["email"]); ?>
                    </div>

                </div>


                <div class="col-md-4">

                    <div class="profile-label">
                        Phone Number
                    </div>

                    <div class="profile-value">
                        <?php echo e($user["phone"]); ?>
                    </div>

                </div>


                <div class="col-md-4">

                    <div class="profile-label">
                        User ID
                    </div>

                    <div class="profile-value">
                        #<?php echo e($user["id"]); ?>
                    </div>

                </div>


                <div class="col-md-4">

                    <div class="profile-label">
                        Role
                    </div>

                    <div class="profile-value">

                        <?php if ($user["role"] === "admin"): ?>

                            <span class="badge bg-dark">
                                Admin
                            </span>

                        <?php else: ?>

                            <span class="badge bg-primary">
                                Customer
                            </span>

                        <?php endif; ?>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="profile-label">
                        Account Status
                    </div>

                    <div class="profile-value">

                        <?php if ($user["status"] === "active"): ?>

                            <span class="badge bg-success">
                                Active
                            </span>

                        <?php else: ?>

                            <span class="badge bg-danger">
                                <?php echo e(ucfirst($user["status"])); ?>
                            </span>

                        <?php endif; ?>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="profile-label">
                        Registered On
                    </div>

                    <div class="profile-value">

                        <?php
                        echo !empty($user["created_at"])
                            ? date("M d, Y h:i A", strtotime($user["created_at"]))
                            : "N/A";
                        ?>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- Bank Account -->

    <div class="card mb-4">

        <div class="card-header bg-white py-3">

            <h5 class="mb-0 fw-bold">
                Bank Account
            </h5>

        </div>

        <div class="card-body">

            <?php if (!empty($user["account_id"])): ?>

                <div class="row">

                    <div class="col-md-4">

                        <div class="profile-label">
                            Account Number
                        </div>

                        <div class="profile-value">
                            <?php echo e($user["account_number"]); ?>
                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="profile-label">
                            Account Type
                        </div>

                        <div class="profile-value">
                            <?php echo e(ucfirst($user["account_type"])); ?>
                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="profile-label">
                            Currency
                        </div>

                        <div class="profile-value">
                            <?php echo e($user["currency"]); ?>
                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="profile-label">
                            Current Balance
                        </div>

                        <div class="profile-value text-success">

                            <?php
                            echo formatMoney(
                                $user["balance"],
                                $user["currency"]
                            );
                            ?>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="profile-label">
                            Account Status
                        </div>

                        <div class="profile-value">

                            <?php if ($user["account_status"] === "active"): ?>

                                <span class="badge bg-success">
                                    Active
                                </span>

                            <?php else: ?>

                                <span class="badge bg-danger">
                                    <?php echo e(ucfirst($user["account_status"])); ?>
                                </span>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            <?php else: ?>

                <div class="alert alert-warning mb-0">
                    This user does not have a bank account.
                </div>

            <?php endif; ?>

        </div>

    </div>


    <!-- Statistics -->

    <div class="row g-4 mb-4">

        <div class="col-md-3">

            <div class="card stat-card">

                <div class="card-body">

                    <div class="stat-title">
                        Total Transactions
                    </div>

                    <div class="stat-value">
                        <?php echo (int)$stats["total_transactions"]; ?>
                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card stat-card">

                <div class="card-body">

                    <div class="stat-title">
                        Total Deposits
                    </div>

                    <div class="stat-value text-success">

                        <?php
                        echo formatMoney(
                            $stats["total_deposits"],
                            $user["currency"] ?? "USD"
                        );
                        ?>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card stat-card">

                <div class="card-body">

                    <div class="stat-title">
                        Total Withdrawals
                    </div>

                    <div class="stat-value text-danger">

                        <?php
                        echo formatMoney(
                            $stats["total_withdrawals"],
                            $user["currency"] ?? "USD"
                        );
                        ?>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card stat-card">

                <div class="card-body">

                    <div class="stat-title">
                        Total Transfers
                    </div>

                    <div class="stat-value text-primary">

                        <?php
                        echo formatMoney(
                            $stats["total_transfers"],
                            $user["currency"] ?? "USD"
                        );
                        ?>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- Transaction History -->

    <div class="card mb-5">

        <div class="card-header bg-white py-3">

            <h5 class="mb-0 fw-bold">
                Recent Transactions
            </h5>

        </div>

        <div class="card-body p-0">

            <?php if (empty($transactions)): ?>

                <div class="p-4">

                    <div class="alert alert-info mb-0">
                        This user has no transactions yet.
                    </div>

                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th>
                                    Reference
                                </th>

                                <th>
                                    Type
                                </th>

                                <th>
                                    Amount
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Description
                                </th>

                                <th>
                                    Date
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($transactions as $transaction): ?>

                                <tr>

                                    <td>

                                        <small class="fw-bold">
                                            <?php
                                            echo e(
                                                $transaction["transaction_reference"]
                                            );
                                            ?>
                                        </small>

                                    </td>


                                    <td>

                                        <?php

                                        $type = $transaction["transaction_type"];

                                        if ($type === "deposit") {

                                            echo '<span class="badge bg-success">Deposit</span>';

                                        } elseif ($type === "withdrawal") {

                                            echo '<span class="badge bg-danger">Withdrawal</span>';

                                        } elseif ($type === "transfer") {

                                            echo '<span class="badge bg-primary">Transfer</span>';

                                        } else {

                                            echo '<span class="badge bg-secondary">' .
                                                e(ucfirst($type)) .
                                                '</span>';
                                        }

                                        ?>

                                    </td>


                                    <td class="fw-bold">

                                        <?php
                                        echo formatMoney(
                                            $transaction["amount"],
                                            $user["currency"] ?? "USD"
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php

                                        $status = $transaction["status"];

                                        if ($status === "completed") {

                                            echo '<span class="badge bg-success">Completed</span>';

                                        } elseif ($status === "pending") {

                                            echo '<span class="badge bg-warning text-dark">Pending</span>';

                                        } elseif ($status === "failed") {

                                            echo '<span class="badge bg-danger">Failed</span>';

                                        } else {

                                            echo '<span class="badge bg-secondary">' .
                                                e(ucfirst($status)) .
                                                '</span>';
                                        }

                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo e(
                                            $transaction["description"] ?: "-"
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <small>

                                            <?php
                                            echo date(
                                                "M d, Y",
                                                strtotime(
                                                    $transaction["created_at"]
                                                )
                                            );
                                            ?>

                                            <br>

                                            <span class="text-muted">

                                                <?php
                                                echo date(
                                                    "h:i A",
                                                    strtotime(
                                                        $transaction["created_at"]
                                                    )
                                                );
                                                ?>

                                            </span>

                                        </small>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>