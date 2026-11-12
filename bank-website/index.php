<?php

require_once "config/config.php";

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo SITE_NAME; ?>
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

<nav class="navbar navbar-expand-lg navbar-dark bg-danger">

    <div class="container">

        <a
            class="navbar-brand fw-bold"
            href="index.php"
        >
            ELLEY-PAY
        </a>

        <button
            class="navbar-toggler"
            data-bs-toggle="collapse"
            data-bs-target="#nav"
        >

            <span class="navbar-toggler-icon"></span>

        </button>

        <div
            class="collapse navbar-collapse"
            id="nav"
        >

            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="index.php"
                    >
                        Home
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="login.php"
                    >
                        Login
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="btn btn-light ms-2"
                        href="register.php"
                    >
                        Open Account
                    </a>
                </li>

            </ul>

        </div>

    </div>

</nav>


<section class="hero">

    <div class="container">

        <div class="row align-items-center">

            <div class="col-lg-6">

                <h1 class="display-4">
                    Banking Made Simple
                </h1>

                <p class="lead mt-3">

                    Manage your money, transfer funds,
                    monitor transactions and control
                    your account from anywhere.

                </p>

                <div class="mt-4">

                    <a
                        href="register.php"
                        class="btn btn-primary btn-lg"
                    >
                        Open an Account
                    </a>

                    <a
                        href="login.php"
                        class="btn btn-outline-primary btn-lg ms-2"
                    >
                        Login
                    </a>

                </div>

            </div>


            <div class="col-lg-6 mt-5 mt-lg-0">

                <div class="bank-card">

                    <h5>ELLEY-PAY</h5>

                    <p>
                        Available Balance
                    </p>

                    <h2>
                        ₦250,000.00
                    </h2>

                    <hr>

                    <p class="mb-0">
                        **** **** **** 1001
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>


<section class="py-5">

    <div class="container">

        <h2 class="text-center mb-5">
            Banking Services
        </h2>

        <div class="row g-4">

            <div class="col-md-4">

                <div class="card action-card h-100">

                    <div class="card-body p-4">

                        <h4>
                            💰 Savings
                        </h4>

                        <p>
                            Manage your savings and keep
                            track of your account balance.
                        </p>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="card action-card h-100">

                    <div class="card-body p-4">

                        <h4>
                            🔄 Transfers
                        </h4>

                        <p>
                            Send money securely to another
                            SecureBank customer.
                        </p>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="card action-card h-100">

                    <div class="card-body p-4">

                        <h4>
                            🔐 Security
                        </h4>

                        <p>
                            Protect your account with
                            password-protected access.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<?php require_once "includes/footer.php"; ?>
