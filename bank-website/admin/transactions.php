<?php

require_once "../includes/auth.php";
require_once "../config/database.php";
require_once "../includes/functions.php";


// --------------------------------------------------
// ADMIN ACCESS ONLY
// --------------------------------------------------

if (
    !isset($_SESSION["user_role"]) ||
    $_SESSION["user_role"] !== "admin"
) {
    header("Location: ../dashboard.php");
    exit;
}


// --------------------------------------------------
// SEARCH AND FILTERS
// --------------------------------------------------

$search = trim($_GET["search"] ?? "");
$type = trim($_GET["type"] ?? "");
$status = trim($_GET["status"] ?? "");


// --------------------------------------------------
// SUMMARY STATISTICS
// --------------------------------------------------

// Total transactions
$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM transactions
");

$totalTransactions = (int)$stmt->fetchColumn();


// Total completed deposits
$stmt = $pdo->query("
    SELECT COALESCE(SUM(amount), 0)
    FROM transactions
    WHERE transaction_type = 'deposit'
    AND status = 'completed'
");

$totalDeposits = (float)$stmt->fetchColumn();


// Total completed withdrawals
$stmt = $pdo->query("
    SELECT COALESCE(SUM(amount), 0)
    FROM transactions
    WHERE transaction_type = 'withdrawal'
    AND status = 'completed'
");

$totalWithdrawals = (float)$stmt->fetchColumn();


// Total completed transfers
$stmt = $pdo->query("
    SELECT COALESCE(SUM(amount), 0)
    FROM transactions
    WHERE transaction_type = 'transfer'
    AND status = 'completed'
");

$totalTransfers = (float)$stmt->fetchColumn();


// --------------------------------------------------
// BUILD TRANSACTION QUERY
// --------------------------------------------------

$sql = "
    SELECT
        t.id,
        t.transaction_reference,
        t.transaction_type,
        t.amount,
        t.status,
        t.description,
        t.created_at,

        a.account_number,
        a.currency,

        u.id AS user_id,
        u.first_name,
        u.last_name,
        u.email

    FROM transactions t

    JOIN accounts a
        ON a.id = t.account_id

    JOIN users u
        ON u.id = a.user_id

    WHERE 1=1
";


$params = [];


// --------------------------------------------------
// SEARCH
// --------------------------------------------------

if ($search !== "") {

    $sql .= "
        AND (
            t.transaction_reference LIKE ?
            OR a.account_number LIKE ?
            OR u.first_name LIKE ?
            OR u.last_name LIKE ?
            OR u.email LIKE ?
            OR t.description LIKE ?
        )
    ";

    $searchValue = "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
}


// --------------------------------------------------
// TRANSACTION TYPE FILTER
// --------------------------------------------------

if (
    $type !== "" &&
    in_array(
        $type,
        ["deposit", "withdrawal", "transfer"],
        true
    )
) {

    $sql .= "
        AND t.transaction_type = ?
    ";

    $params[] = $type;
}


// --------------------------------------------------
// STATUS FILTER
// --------------------------------------------------

if (
    $status !== "" &&
    in_array(
        $status,
        ["completed", "pending", "failed", "cancelled"],
        true
    )
) {

    $sql .= "
        AND t.status = ?
    ";

    $params[] = $status;
}


// --------------------------------------------------
// ORDER
// --------------------------------------------------

$sql .= "
    ORDER BY t.created_at DESC
    LIMIT 200
";


// --------------------------------------------------
// EXECUTE
// --------------------------------------------------

$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$transactions = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Transactions - ELLEY-PAY Admin</title>


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

        .page-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,.06);
        }

        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,.06);
            min-height: 125px;
        }

        .stat-title {
            color: #6b7280;
            font-size: 14px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
        }

        .table {
            vertical-align: middle;
        }

        .reference {
            font-size: 12px;
            font-weight: 600;
        }

        .account-number {
            font-weight: 600;
            letter-spacing: .4px;
        }

        .badge {
            text-transform: capitalize;
        }

    </style>

</head>


<body>


<!-- ==================================================
     ADMIN NAVBAR
================================================== -->

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
                        class="nav-link active"
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
                        Customer Site
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



<!-- ==================================================
     MAIN CONTENT
================================================== -->

<div class="container py-5">


    <!-- PAGE HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-bold mb-1">
                All Transactions
            </h2>

            <p class="text-muted mb-0">
                Monitor all ELLEY-PAY customer transactions.
            </p>

        </div>


        <a
            href="dashboard.php"
            class="btn btn-outline-dark"
        >
            ← Dashboard
        </a>

    </div>



    <!-- ==================================================
         STATISTICS
    ================================================== -->

    <div class="row g-4 mb-4">


        <!-- TOTAL -->

        <div class="col-md-6 col-lg-3">

            <div class="card stat-card">

                <div class="card-body">

                    <div class="stat-title">
                        Total Transactions
                    </div>

                    <div class="stat-value mt-2">
                        <?php
                        echo number_format($totalTransactions);
                        ?>
                    </div>

                </div>

            </div>

        </div>


        <!-- DEPOSITS -->

        <div class="col-md-6 col-lg-3">

            <div class="card stat-card">

                <div class="card-body">

                    <div class="stat-title">
                        Total Deposits
                    </div>

                    <div class="stat-value mt-2 text-success">

                        $<?php
                        echo number_format(
                            $totalDeposits,
                            2
                        );
                        ?>

                    </div>

                </div>

            </div>

        </div>


        <!-- WITHDRAWALS -->

        <div class="col-md-6 col-lg-3">

            <div class="card stat-card">

                <div class="card-body">

                    <div class="stat-title">
                        Total Withdrawals
                    </div>

                    <div class="stat-value mt-2 text-danger">

                        $<?php
                        echo number_format(
                            $totalWithdrawals,
                            2
                        );
                        ?>

                    </div>

                </div>

            </div>

        </div>


        <!-- TRANSFERS -->

        <div class="col-md-6 col-lg-3">

            <div class="card stat-card">

                <div class="card-body">

                    <div class="stat-title">
                        Total Transfers
                    </div>

                    <div class="stat-value mt-2">

                        $<?php
                        echo number_format(
                            $totalTransfers,
                            2
                        );
                        ?>

                    </div>

                </div>

            </div>

        </div>


    </div>



    <!-- ==================================================
         SEARCH AND FILTERS
    ================================================== -->

    <div class="card page-card mb-4">

        <div class="card-body">

            <form
                method="GET"
                class="row g-3"
            >


                <!-- SEARCH -->

                <div class="col-md-5">

                    <label class="form-label">
                        Search
                    </label>

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Reference, name, email or account number..."
                        value="<?php echo e($search); ?>"
                    >

                </div>


                <!-- TYPE -->

                <div class="col-md-3">

                    <label class="form-label">
                        Transaction Type
                    </label>

                    <select
                        name="type"
                        class="form-select"
                    >

                        <option value="">
                            All Types
                        </option>

                        <option
                            value="deposit"
                            <?php
                            echo $type === "deposit"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Deposit
                        </option>

                        <option
                            value="withdrawal"
                            <?php
                            echo $type === "withdrawal"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Withdrawal
                        </option>

                        <option
                            value="transfer"
                            <?php
                            echo $type === "transfer"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Transfer
                        </option>

                    </select>

                </div>


                <!-- STATUS -->

                <div class="col-md-2">

                    <label class="form-label">
                        Status
                    </label>

                    <select
                        name="status"
                        class="form-select"
                    >

                        <option value="">
                            All Statuses
                        </option>

                        <option
                            value="completed"
                            <?php
                            echo $status === "completed"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Completed
                        </option>

                        <option
                            value="pending"
                            <?php
                            echo $status === "pending"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Pending
                        </option>

                        <option
                            value="failed"
                            <?php
                            echo $status === "failed"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Failed
                        </option>

                        <option
                            value="cancelled"
                            <?php
                            echo $status === "cancelled"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Cancelled
                        </option>

                    </select>

                </div>


                <!-- BUTTONS -->

                <div class="col-md-2 d-flex align-items-end gap-2">

                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                    >
                        Search
                    </button>


                    <a
                        href="transactions.php"
                        class="btn btn-outline-secondary"
                    >
                        Reset
                    </a>

                </div>


            </form>

        </div>

    </div>



    <!-- ==================================================
         TRANSACTIONS TABLE
    ================================================== -->

    <div class="card page-card">

        <div class="card-body">


            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">
                    Transaction Records
                </h5>


                <span class="badge bg-dark">

                    <?php
                    echo number_format(
                        count($transactions)
                    );
                    ?>

                    Result<?php
                    echo count($transactions) !== 1
                        ? "s"
                        : "";
                    ?>

                </span>

            </div>



            <div class="table-responsive">

                <table class="table table-hover">

                    <thead>

                        <tr>

                            <th>Reference</th>

                            <th>Customer</th>

                            <th>Account</th>

                            <th>Type</th>

                            <th>Amount</th>

                            <th>Status</th>

                            <th>Description</th>

                            <th>Date</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (empty($transactions)): ?>

                        <tr>

                            <td
                                colspan="8"
                                class="text-center py-5 text-muted"
                            >

                                No transactions found.

                            </td>

                        </tr>


                    <?php else: ?>


                        <?php foreach ($transactions as $transaction): ?>

                            <tr>


                                <!-- REFERENCE -->

                                <td>

                                    <span class="reference">

                                        <?php
                                        echo e(
                                            $transaction[
                                                "transaction_reference"
                                            ]
                                        );
                                        ?>

                                    </span>

                                </td>



                                <!-- CUSTOMER -->

                                <td>

                                    <strong>

                                        <?php
                                        echo e(
                                            $transaction["first_name"] .
                                            " " .
                                            $transaction["last_name"]
                                        );
                                        ?>

                                    </strong>

                                    <br>

                                    <small class="text-muted">

                                        <?php
                                        echo e(
                                            $transaction["email"]
                                        );
                                        ?>

                                    </small>

                                </td>



                                <!-- ACCOUNT -->

                                <td>

                                    <span class="account-number">

                                        <?php
                                        echo e(
                                            $transaction[
                                                "account_number"
                                            ]
                                        );
                                        ?>

                                    </span>

                                </td>



                                <!-- TYPE -->

                                <td>

                                    <?php

                                    $transactionType =
                                        $transaction[
                                            "transaction_type"
                                        ];


                                    if (
                                        $transactionType ===
                                        "deposit"
                                    ) {

                                        echo '
                                            <span class="badge bg-success">
                                                Deposit
                                            </span>
                                        ';

                                    }

                                    elseif (
                                        $transactionType ===
                                        "withdrawal"
                                    ) {

                                        echo '
                                            <span class="badge bg-danger">
                                                Withdrawal
                                            </span>
                                        ';

                                    }

                                    elseif (
                                        $transactionType ===
                                        "transfer"
                                    ) {

                                        echo '
                                            <span class="badge bg-primary">
                                                Transfer
                                            </span>
                                        ';

                                    }

                                    else {

                                        echo '
                                            <span class="badge bg-secondary">
                                                ' .
                                                e($transactionType) .
                                                '
                                            </span>
                                        ';

                                    }

                                    ?>

                                </td>



                                <!-- AMOUNT -->

                                <td>

                                    <?php

                                    $currency =
                                        $transaction["currency"]
                                        ?? "USD";

                                    echo formatMoney(
                                        $transaction["amount"],
                                        $currency
                                    );

                                    ?>

                                </td>



                                <!-- STATUS -->

                                <td>

                                    <?php

                                    $transactionStatus =
                                        $transaction["status"];


                                    if (
                                        $transactionStatus ===
                                        "completed"
                                    ) {

                                        echo '
                                            <span class="badge bg-success">
                                                Completed
                                            </span>
                                        ';

                                    }

                                    elseif (
                                        $transactionStatus ===
                                        "pending"
                                    ) {

                                        echo '
                                            <span class="badge bg-warning text-dark">
                                                Pending
                                            </span>
                                        ';

                                    }

                                    elseif (
                                        $transactionStatus ===
                                        "failed"
                                    ) {

                                        echo '
                                            <span class="badge bg-danger">
                                                Failed
                                            </span>
                                        ';

                                    }

                                    else {

                                        echo '
                                            <span class="badge bg-secondary">
                                                ' .
                                                e($transactionStatus) .
                                                '
                                            </span>
                                        ';

                                    }

                                    ?>

                                </td>



                                <!-- DESCRIPTION -->

                                <td>

                                    <small>

                                        <?php

                                        echo e(
                                            $transaction[
                                                "description"
                                            ] ?? ""
                                        );

                                        ?>

                                    </small>

                                </td>



                                <!-- DATE -->

                                <td>

                                    <small>

                                        <?php

                                        echo date(
                                            "M d, Y",
                                            strtotime(
                                                $transaction[
                                                    "created_at"
                                                ]
                                            )
                                        );

                                        ?>

                                        <br>

                                        <?php

                                        echo date(
                                            "h:i A",
                                            strtotime(
                                                $transaction[
                                                    "created_at"
                                                ]
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