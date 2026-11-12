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
// VARIABLES
// --------------------------------------------------

$error = "";
$success = "";


// --------------------------------------------------
// ACTIVATE / DEACTIVATE USER
// --------------------------------------------------

if (
    isset($_GET["action"]) &&
    isset($_GET["id"])
) {

    $action = $_GET["action"];
    $userId = (int) $_GET["id"];


    // Never allow admin to modify their own account
    if ($userId === (int)$_SESSION["user_id"]) {

        $error = "You cannot change the status of your own account.";

    } else {

        if ($action === "activate") {

            $stmt = $pdo->prepare("
                UPDATE users
                SET status = 'active'
                WHERE id = ?
            ");

            $stmt->execute([$userId]);

            $success = "User account activated successfully.";

        }

        elseif ($action === "deactivate") {

            $stmt = $pdo->prepare("
                UPDATE users
                SET status = 'inactive'
                WHERE id = ?
            ");

            $stmt->execute([$userId]);

            $success = "User account deactivated successfully.";
        }
    }
}


// --------------------------------------------------
// DELETE USER
// --------------------------------------------------

if (
    isset($_GET["delete"]) &&
    isset($_GET["id"])
) {

    $userId = (int) $_GET["id"];


    // Never allow admin to delete themselves
    if ($userId === (int)$_SESSION["user_id"]) {

        $error = "You cannot delete your own administrator account.";

    } else {

        try {

            $pdo->beginTransaction();


            // Find user's account
            $accountStmt = $pdo->prepare("
                SELECT id
                FROM accounts
                WHERE user_id = ?
            ");

            $accountStmt->execute([$userId]);

            $account = $accountStmt->fetch();


            /*
             * Delete transactions first if an account exists.
             */
            if ($account) {

                $transactionStmt = $pdo->prepare("
                    DELETE FROM transactions
                    WHERE account_id = ?
                ");

                $transactionStmt->execute([
                    $account["id"]
                ]);


                // Delete account
                $deleteAccount = $pdo->prepare("
                    DELETE FROM accounts
                    WHERE id = ?
                ");

                $deleteAccount->execute([
                    $account["id"]
                ]);
            }


            // Delete user
            $deleteUser = $pdo->prepare("
                DELETE FROM users
                WHERE id = ?
            ");

            $deleteUser->execute([$userId]);


            $pdo->commit();

            $success = "User deleted successfully.";

        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = "Unable to delete user.";
        }
    }
}


// --------------------------------------------------
// SEARCH
// --------------------------------------------------

$search = trim($_GET["search"] ?? "");


// --------------------------------------------------
// GET USERS
// --------------------------------------------------

if ($search !== "") {

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

        WHERE
            u.first_name LIKE ?
            OR u.last_name LIKE ?
            OR u.email LIKE ?
            OR u.phone LIKE ?
            OR a.account_number LIKE ?

        ORDER BY u.id DESC
    ");

    $searchValue = "%" . $search . "%";

    $stmt->execute([
        $searchValue,
        $searchValue,
        $searchValue,
        $searchValue,
        $searchValue
    ]);

} else {

    $stmt = $pdo->query("
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

        ORDER BY u.id DESC
    ");
}


$users = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Users - ELLEY-PAY Admin</title>


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

        .table {
            vertical-align: middle;
        }

        .account-number {
            font-weight: 600;
            letter-spacing: .5px;
        }

        .user-name {
            font-weight: 600;
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
                        class="nav-link active"
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
                Manage Users
            </h2>

            <p class="text-muted mb-0">
                View and manage ELLEY-PAY customer accounts.
            </p>

        </div>


        <a
            href="dashboard.php"
            class="btn btn-outline-dark"
        >
            ← Dashboard
        </a>

    </div>



    <!-- ALERTS -->

    <?php if (!empty($error)): ?>

        <div class="alert alert-danger alert-dismissible fade show">

            <?php echo e($error); ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <?php if (!empty($success)): ?>

        <div class="alert alert-success alert-dismissible fade show">

            <?php echo e($success); ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>



    <!-- SEARCH -->

    <div class="card page-card mb-4">

        <div class="card-body">

            <form
                method="GET"
                class="row g-2"
            >

                <div class="col-md-10">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search by name, email, phone or account number..."
                        value="<?php echo e($search); ?>"
                    >

                </div>


                <div class="col-md-2">

                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                    >
                        Search
                    </button>

                </div>

            </form>

        </div>

    </div>



    <!-- USERS TABLE -->

    <div class="card page-card">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">
                    All Users
                </h5>

                <span class="badge bg-dark">

                    <?php echo number_format(count($users)); ?>

                    User<?php echo count($users) !== 1 ? "s" : ""; ?>

                </span>

            </div>


            <div class="table-responsive">

                <table class="table table-hover">

                    <thead>

                        <tr>

                            <th>#</th>

                            <th>User</th>

                            <th>Email</th>

                            <th>Account Number</th>

                            <th>Balance</th>

                            <th>Role</th>

                            <th>Status</th>

                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (empty($users)): ?>

                        <tr>

                            <td
                                colspan="8"
                                class="text-center py-5 text-muted"
                            >

                                No users found.

                            </td>

                        </tr>

                    <?php else: ?>


                        <?php foreach ($users as $user): ?>

                            <tr>


                                <!-- ID -->

                                <td>

                                    <?php
                                    echo (int)$user["id"];
                                    ?>

                                </td>



                                <!-- USER -->

                                <td>

                                    <div class="user-name">

                                        <?php
                                        echo e(
                                            $user["first_name"] .
                                            " " .
                                            $user["last_name"]
                                        );
                                        ?>

                                    </div>

                                    <small class="text-muted">

                                        <?php
                                        echo e(
                                            $user["phone"] ?? ""
                                        );
                                        ?>

                                    </small>

                                </td>



                                <!-- EMAIL -->

                                <td>

                                    <?php
                                    echo e($user["email"]);
                                    ?>

                                </td>



                                <!-- ACCOUNT -->

                                <td>

                                    <?php if (!empty($user["account_number"])): ?>

                                        <span class="account-number">

                                            <?php
                                            echo e(
                                                $user["account_number"]
                                            );
                                            ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No account
                                        </span>

                                    <?php endif; ?>

                                </td>



                                <!-- BALANCE -->

                                <td>

                                    <?php

                                    $currency =
                                        $user["currency"] ?? "USD";

                                    $balance =
                                        $user["balance"] ?? 0;

                                    echo formatMoney(
                                        $balance,
                                        $currency
                                    );

                                    ?>

                                </td>



                                <!-- ROLE -->

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



                                <!-- STATUS -->

                                <td>

                                    <?php if ($user["status"] === "active"): ?>

                                        <span class="badge bg-success">
                                            Active
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-danger">
                                            Inactive
                                        </span>

                                    <?php endif; ?>

                                </td>



                                <!-- ACTIONS -->

                                <td>

                                    <div class="dropdown">

                                        <button
                                            class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                        >

                                            Actions

                                        </button>


                                        <ul class="dropdown-menu dropdown-menu-end">


                                            <!-- VIEW -->

                                            <li>

                                                <a
                                                    class="dropdown-item"
                                                    href="user-view.php?id=<?php echo (int)$user["id"]; ?>"
                                                >
                                                    View User
                                                </a>

                                            </li>


                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>


                                            <!-- ACTIVATE -->

                                            <?php if ($user["status"] !== "active"): ?>

                                                <li>

                                                    <a
                                                        class="dropdown-item text-success"
                                                        href="users.php?action=activate&id=<?php echo (int)$user["id"]; ?>"
                                                        onclick="return confirm('Activate this user account?');"
                                                    >

                                                        Activate User

                                                    </a>

                                                </li>

                                            <?php endif; ?>



                                            <!-- DEACTIVATE -->

                                            <?php if (
                                                $user["status"] === "active" &&
                                                $user["id"] != $_SESSION["user_id"]
                                            ): ?>

                                                <li>

                                                    <a
                                                        class="dropdown-item text-warning"
                                                        href="users.php?action=deactivate&id=<?php echo (int)$user["id"]; ?>"
                                                        onclick="return confirm('Deactivate this user account?');"
                                                    >

                                                        Deactivate User

                                                    </a>

                                                </li>

                                            <?php endif; ?>



                                            <!-- DELETE -->

                                            <?php if (
                                                $user["id"] != $_SESSION["user_id"] &&
                                                $user["role"] !== "admin"
                                            ): ?>

                                                <li>

                                                    <hr class="dropdown-divider">

                                                </li>

                                                <li>

                                                    <a
                                                        class="dropdown-item text-danger"
                                                        href="users.php?delete=1&id=<?php echo (int)$user["id"]; ?>"
                                                        onclick="return confirm('WARNING: This will permanently delete the user, their account and transaction history. Continue?');"
                                                    >

                                                        Delete User

                                                    </a>

                                                </li>

                                            <?php endif; ?>


                                        </ul>

                                    </div>

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