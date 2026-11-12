<?php

require_once "../includes/auth.php";
require_once "../config/database.php";
require_once "../includes/functions.php";

// Make sure only admins can access this page
if (
    !isset($_SESSION["user_role"]) ||
    $_SESSION["user_role"] !== "admin"
) {
    header("Location: ../dashboard.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Total Users
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM users
");

$totalUsers = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Total Accounts
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM accounts
");

$totalAccounts = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Total Balance
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COALESCE(SUM(balance), 0)
    FROM accounts
    WHERE account_status = 'active'
");

$totalBalance = (float) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Total Deposits
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COALESCE(SUM(amount), 0)
    FROM transactions
    WHERE transaction_type = 'deposit'
    AND status = 'completed'
");

$totalDeposits = (float) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Total Withdrawals
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COALESCE(SUM(amount), 0)
    FROM transactions
    WHERE transaction_type = 'withdrawal'
    AND status = 'completed'
");

$totalWithdrawals = (float) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Total Transfers
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COALESCE(SUM(amount), 0)
    FROM transactions
    WHERE transaction_type = 'transfer'
    AND status = 'completed'
");

$totalTransfers = (float) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Recent Transactions
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        t.id,
        t.transaction_reference,
        t.transaction_type,
        t.amount,
        t.status,
        t.description,
        t.created_at,
        a.account_number,
        u.first_name,
        u.last_name
    FROM transactions t
    JOIN accounts a
        ON a.id = t.account_id
    JOIN users u
        ON u.id = a.user_id
    ORDER BY t.created_at DESC
    LIMIT 10
");

$recentTransactions = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Recent Users
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        u.id,
        u.first_name,
        u.last_name,
        u.email,
        u.role,
        u.status,
        u.created_at,
        a.account_number,
        a.balance,
        a.currency
    FROM users u
    LEFT JOIN accounts a
        ON a.user_id = u.id
    ORDER BY u.created_at DESC
    LIMIT 5
");

$recentUsers = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Dashboard - ELLEY-PAY</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background: #f5f7fb;
        }

        .admin-navbar {
            background: #0000fff9;
        }

        .admin-navbar .navbar-brand {
            color: white;
            font-weight: 700;
        }

        .admin-navbar .nav-link {
            color: rgba(255,255,255,.8);
        }

        .admin-navbar .nav-link:hover {
            color: white;
        }

        .dashboard-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,.06);
        }

        .stat-card {
            min-height: 140px;
        }

        .stat-title {
            color: #6b7280;
            font-size: 14px;
        }

        .stat-value {
            font-size: 25px;
            font-weight: 700;
        }

        .table-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,.06);
        }

        .badge {
            text-transform: capitalize;
        }

        .admin-title {
            font-weight: 700;
        }

    </style>

</head>


<body>


<!-- ADMIN NAVBAR -->

<nav class="navbar navbar-expand-lg admin-navbar">

    <div class="container">

        <a
            class="navbar-brand"
            href="dashboard.php"
        >
            ELLEY-PAY Admin
        </a>


        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#adminNav"
        >
            <span class="navbar-toggler-icon"></span>
        </button>


        <div
            class="collapse navbar-collapse"
            id="adminNav"
        >

            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="dashboard.php"
                    >
                        Dashboard
                    </a>
                </li>


                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="users.php"
                    >
                        Users
                    </a>
                </li>


                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="transactions.php"
                    >
                        Transactions
                    </a>
                </li>


                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="../dashboard.php"
                    >
                        Admin Site
                    </a>
                </li>


                <li class="nav-item">
                    <a
                        class="nav-link text-warning"
                        href="logout.php"
                    >
                        Logout
                    </a>
                </li>

            </ul>

        </div>

    </div>

</nav>



<!-- MAIN CONTENT -->

<div class="container py-5">


    <!-- HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="admin-title">
                Admin Dashboard
            </h2>

            <p class="text-muted mb-0">
                Welcome back,
                <?php echo e($_SESSION["user_name"] ?? "Administrator"); ?>.
            </p>

        </div>


        <div>

            <span class="badge bg-dark p-2">
                Administrator
            </span>

        </div>

    </div>



    <!-- STAT CARDS -->

    <div class="row g-4 mb-4">


        <!-- USERS -->

        <div class="col-md-6 col-lg-3">

            <div class="card dashboard-card stat-card">

                <div class="card-body">

                    <div class="stat-title">
                        Total Users
                    </div>

                    <div class="stat-value mt-2">
                        <?php echo number_format($totalUsers); ?>
                    </div>

                    <a
                        href="users.php"
                        class="small text-decoration-none"
                    >
                        Manage Users →
                    </a>

                </div>

            </div>

        </div>



        <!-- ACCOUNTS -->

        <div class="col-md-6 col-lg-3">

            <div class="card dashboard-card stat-card">

                <div class="card-body">

                    <div class="stat-title">
                        Total Accounts
                    </div>

                    <div class="stat-value mt-2">
                        <?php echo number_format($totalAccounts); ?>
                    </div>

                    <span class="small text-muted">
                        Registered bank accounts
                    </span>

                </div>

            </div>

        </div>



        <!-- BALANCE -->

        <div class="col-md-6 col-lg-3">

            <div class="card dashboard-card stat-card">

                <div class="card-body">

                    <div class="stat-title">
                        Total Balance
                    </div>

                    <div class="stat-value mt-2">
                        $<?php echo number_format($totalBalance, 2); ?>
                    </div>

                    <span class="small text-muted">
                        Active accounts
                    </span>

                </div>

            </div>

        </div>



        <!-- DEPOSITS -->

        <div class="col-md-6 col-lg-3">

            <div class="card dashboard-card stat-card">

                <div class="card-body">

                    <div class="stat-title">
                        Total Deposits
                    </div>

                    <div class="stat-value mt-2">
                        $<?php echo number_format($totalDeposits, 2); ?>
                    </div>

                    <span class="small text-muted">
                        Completed deposits
                    </span>

                </div>

            </div>

        </div>

    </div>



    <!-- SECOND STAT ROW -->

    <div class="row g-4 mb-5">


        <!-- WITHDRAWALS -->

        <div class="col-md-6 col-lg-4">

            <div class="card dashboard-card">

                <div class="card-body">

                    <div class="stat-title">
                        Total Withdrawals
                    </div>

                    <div class="stat-value mt-2">
                        $<?php echo number_format($totalWithdrawals, 2); ?>
                    </div>

                </div>

            </div>

        </div>



        <!-- TRANSFERS -->

        <div class="col-md-6 col-lg-4">

            <div class="card dashboard-card">

                <div class="card-body">

                    <div class="stat-title">
                        Total Transfers
                    </div>

                    <div class="stat-value mt-2">
                        $<?php echo number_format($totalTransfers, 2); ?>
                    </div>

                </div>

            </div>

        </div>



        <!-- QUICK ACTION -->

        <div class="col-md-12 col-lg-4">

            <div class="card dashboard-card">

                <div class="card-body">

                    <div class="stat-title mb-3">
                        Quick Actions
                    </div>

                    <a
                        href="users.php"
                        class="btn btn-primary btn-sm me-2"
                    >
                        Manage Users
                    </a>

                    <a
                        href="transactions.php"
                        class="btn btn-outline-dark btn-sm"
                    >
                        Transactions
                    </a>

                </div>

            </div>

        </div>

    </div>



    <!-- RECENT TRANSACTIONS -->

    <div class="card table-card mb-5">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">
                    Recent Transactions
                </h5>

                <a
                    href="transactions.php"
                    class="btn btn-sm btn-outline-primary"
                >
                    View All
                </a>

            </div>


            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>

                        <tr>

                            <th>Reference</th>

                            <th>User</th>

                            <th>Account</th>

                            <th>Type</th>

                            <th>Amount</th>

                            <th>Status</th>

                            <th>Date</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if (empty($recentTransactions)): ?>

                        <tr>

                            <td
                                colspan="7"
                                class="text-center text-muted py-4"
                            >
                                No transactions found.
                            </td>

                        </tr>

                    <?php else: ?>

                        <?php foreach ($recentTransactions as $transaction): ?>

                            <tr>

                                <td>
                                    <small>
                                        <?php
                                        echo e(
                                            $transaction["transaction_reference"]
                                        );
                                        ?>
                                    </small>
                                </td>


                                <td>

                                    <?php
                                    echo e(
                                        $transaction["first_name"] .
                                        " " .
                                        $transaction["last_name"]
                                    );
                                    ?>

                                </td>


                                <td>
                                    <?php
                                    echo e(
                                        $transaction["account_number"]
                                    );
                                    ?>
                                </td>


                                <td>

                                    <?php

                                    $type = $transaction["transaction_type"];

                                    if ($type === "deposit") {
                                        echo '<span class="badge bg-success">Deposit</span>';
                                    }

                                    elseif ($type === "withdrawal") {
                                        echo '<span class="badge bg-danger">Withdrawal</span>';
                                    }

                                    else {
                                        echo '<span class="badge bg-primary">' .
                                            e($type) .
                                            '</span>';
                                    }

                                    ?>

                                </td>


                                <td>

                                    $<?php
                                    echo number_format(
                                        (float)$transaction["amount"],
                                        2
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php if ($transaction["status"] === "completed"): ?>

                                        <span class="badge bg-success">
                                            Completed
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-warning text-dark">
                                            <?php
                                            echo e(
                                                $transaction["status"]
                                            );
                                            ?>
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <small>

                                        <?php
                                        echo date(
                                            "M d, Y h:i A",
                                            strtotime(
                                                $transaction["created_at"]
                                            )
                                        );
                                        ?>

                                    </small>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>



    <!-- RECENT USERS -->

    <div class="card table-card">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">
                    Recently Registered Users
                </h5>

                <a
                    href="users.php"
                    class="btn btn-sm btn-outline-primary"
                >
                    View All Users
                </a>

            </div>


            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>

                        <tr>

                            <th>Name</th>

                            <th>Email</th>

                            <th>Account Number</th>

                            <th>Balance</th>

                            <th>Role</th>

                            <th>Status</th>

                            <th>Joined</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if (empty($recentUsers)): ?>

                        <tr>

                            <td
                                colspan="7"
                                class="text-center text-muted py-4"
                            >
                                No users found.
                            </td>

                        </tr>

                    <?php else: ?>

                        <?php foreach ($recentUsers as $user): ?>

                            <tr>

                                <td>

                                    <strong>

                                        <?php
                                        echo e(
                                            $user["first_name"] .
                                            " " .
                                            $user["last_name"]
                                        );
                                        ?>

                                    </strong>

                                </td>


                                <td>

                                    <?php
                                    echo e($user["email"]);
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo e(
                                        $user["account_number"] ?? "N/A"
                                    );
                                    ?>

                                </td>


                                <td>

                                    $<?php
                                    echo number_format(
                                        (float)($user["balance"] ?? 0),
                                        2
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php if ($user["role"] === "admin"): ?>

                                        <span class="badge bg-dark">
                                            Admin
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">
                                            User
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php if ($user["status"] === "active"): ?>

                                        <span class="badge bg-success">
                                            Active
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-danger">
                                            <?php
                                            echo e($user["status"]);
                                            ?>
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <small>

                                        <?php
                                        echo date(
                                            "M d, Y",
                                            strtotime(
                                                $user["created_at"]
                                            )
                                        );
                                        ?>

                                    </small>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>



<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>