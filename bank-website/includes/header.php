<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        ELLEY-PAY
    </title>    

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>

<body>


<nav class="navbar navbar-expand-lg navbar-dark bg-primary">

    <div class="container">

        <a
            class="navbar-brand fw-bold"
            href="dashboard.php"
        >
            ELLEY-PAY
        </a>


        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNav"
        >

            <span class="navbar-toggler-icon"></span>

        </button>


        <div
            class="collapse navbar-collapse"
            id="mainNav"
        >

            <ul class="navbar-nav ms-auto">


                <?php if (isset($_SESSION["user_id"])): ?>


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
                            href="deposit.php"
                        >
                            Deposit
                        </a>

                    </li>


                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="withdraw.php"
                        >
                            Withdraw
                        </a>

                    </li>


                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="transfer.php"
                        >
                            Transfer
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
                            href="profile.php"
                        >
                            Profile
                        </a>

                    </li>


                    <?php if (
                        isset($_SESSION["user_role"])
                        &&
                        $_SESSION["user_role"] === "admin"
                    ): ?>

                        <li class="nav-item">

                            <a
                                class="nav-link"
                                href="admin/dashboard.php"
                            >
                                Admin
                            </a>

                        </li>

                    <?php endif; ?>


                    <li class="nav-item">

                        <a
                            class="nav-link text-warning"
                            href="logout.php"
                        >
                            Logout
                        </a>

                    </li>


                <?php endif; ?>

            </ul>

        </div>

    </div>

</nav>