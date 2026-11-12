<?php

session_start();

require_once "config/database.php";
require_once "includes/functions.php";

if (isset($_SESSION["user_id"])) {

    redirect("dashboard.php");

}


$error = "";
$success = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $firstName =
        trim($_POST["first_name"] ?? "");

    $lastName =
        trim($_POST["last_name"] ?? "");

    $email =
        trim($_POST["email"] ?? "");

    $phone =
        trim($_POST["phone"] ?? "");

    $password =
        $_POST["password"] ?? "";

    $confirmPassword =
        $_POST["confirm_password"] ?? "";



    if (
        empty($firstName)
        ||
        empty($lastName)
        ||
        empty($email)
        ||
        empty($password)
    ) {

        $error =
            "Please fill in all required fields.";

    } elseif (!filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )) {

        $error =
            "Please enter a valid email address.";

    } elseif (strlen($password) < 6) {

        $error =
            "Password must be at least 6 characters.";

    } elseif (
        $password !== $confirmPassword
    ) {

        $error =
            "Passwords do not match.";

    } else {

        $check = $pdo->prepare("
            SELECT id
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $check->execute([
            $email
        ]);


        if ($check->fetch()) {

            $error =
                "An account with this email already exists.";

        } else {

            try {

                $pdo->beginTransaction();


                $hashedPassword =
                    password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );


                $userStmt = $pdo->prepare("
                    INSERT INTO users
                    (
                        first_name,
                        last_name,
                        email,
                        phone,
                        password,
                        role,
                        status
                    )
                    VALUES (?, ?, ?, ?, ?, 'customer', 'active')
                ");


                $userStmt->execute([
                    $firstName,
                    $lastName,
                    $email,
                    $phone,
                    $hashedPassword
                ]);


                $userId =
                    $pdo->lastInsertId();


                $accountNumber =
                    generateAccountNumber($pdo);


                $accountStmt = $pdo->prepare("
                    INSERT INTO accounts
                    (
                        user_id,
                        account_number,
                        account_type,
                        balance,
                        currency,
                        account_status
                    )
                    VALUES (?, ?, 'savings', 0.00, 'USD', 'active')
                ");


                $accountStmt->execute([
                    $userId,
                    $accountNumber
                ]);


                $pdo->commit();


                $success =
                    "Registration successful. You can now login.";

            } catch (Exception $e) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $error =
                    "Registration failed. Please try again.";
            }
        }
    }
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
        Create Account - SecureBank
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body class="bg-light">


<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-md-6">


            <div class="card shadow border-0">

                <div class="card-body p-5">


                    <h2 class="text-center">
                        Create Account
                    </h2>

                    <p class="text-center text-muted">
                        Open your SecureBank account
                    </p>


                    <?php if ($error): ?>

                        <div class="alert alert-danger">

                            <?php echo e($error); ?>

                        </div>

                    <?php endif; ?>


                    <?php if ($success): ?>

                        <div class="alert alert-success">

                            <?php echo e($success); ?>

                            <br><br>

                            <a href="login.php">
                                Login Now
                            </a>

                        </div>

                    <?php endif; ?>


                    <form method="POST">


                        <div class="row">


                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    First Name
                                </label>

                                <input
                                    type="text"
                                    name="first_name"
                                    class="form-control"
                                    required
                                >

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Last Name
                                </label>

                                <input
                                    type="text"
                                    name="last_name"
                                    class="form-control"
                                    required
                                >

                            </div>


                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Email Address
                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                required
                            >

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Phone Number
                            </label>

                            <input
                                type="text"
                                name="phone"
                                class="form-control"
                            >

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                minlength="6"
                                required
                            >

                        </div>


                        <div class="mb-4">

                            <label class="form-label">
                                Confirm Password
                            </label>

                            <input
                                type="password"
                                name="confirm_password"
                                class="form-control"
                                minlength="6"
                                required
                            >

                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            Create Account
                        </button>


                    </form>


                    <p class="text-center mt-4 mb-0">

                        Already have an account?

                        <a href="login.php">
                            Login
                        </a>

                    </p>


                </div>

            </div>


        </div>

    </div>

</div>


</body>

</html>