<?php

session_start();

require_once "config/database.php";
require_once "includes/functions.php";

if (isset($_SESSION["user_id"])) {

    if (
        isset($_SESSION["user_role"])
        &&
        $_SESSION["user_role"] === "admin"
    ) {

        redirect("admin/dashboard.php");

    }

    redirect("dashboard.php");
}


$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email =
        trim($_POST["email"] ?? "");

    $password =
        $_POST["password"] ?? "";


    if (
        empty($email)
        ||
        empty($password)
    ) {

        $error =
            "Please enter your email and password.";

    } else {

        $stmt = $pdo->prepare("
            SELECT *
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->execute([
            $email
        ]);

        $user = $stmt->fetch();


        if (!$user) {

            $error =
                "Invalid email or password.";

        } elseif (
            !password_verify(
                $password,
                $user["password"]
            )
        ) {

            $error =
                "Invalid email or password.";

        } elseif (
            $user["status"] !== "active"
        ) {

            $error =
                "Your account is not active.";

        } else {

            session_regenerate_id(true);

            $_SESSION["user_id"] =
                $user["id"];

            $_SESSION["user_name"] =
                $user["first_name"]
                . " "
                . $user["last_name"];

            $_SESSION["user_email"] =
                $user["email"];

            $_SESSION["user_role"] =
                $user["role"];


            if (
                $user["role"] === "admin"
            ) {

                redirect(
                    "admin/dashboard.php"
                );

            } else {

                redirect(
                    "dashboard.php"
                );

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
        Login - SecureBank
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body class="bg-light">


<div class="container">

    <div
        class="row justify-content-center align-items-center"
        style="min-height:100vh;"
    >

        <div class="col-md-5">


            <div class="card shadow border-0">

                <div class="card-body p-5">


                    <h2 class="text-center mb-2">
                        SecureBank
                    </h2>

                    <p class="text-center text-muted mb-4">
                        Login to your account
                    </p>


                    <?php if ($error): ?>

                        <div class="alert alert-danger">

                            <?php echo e($error); ?>

                        </div>

                    <?php endif; ?>


                    <form method="POST">


                        <div class="mb-3">

                            <label class="form-label">
                                Email Address
                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="<?php echo e($_POST["email"] ?? ""); ?>"
                                required
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
                                required
                            >

                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            Login
                        </button>


                    </form>


                    <div class="text-center mt-4">

                        <p>
                            Don't have an account?
                            <a href="register.php">
                                Create Account
                            </a>
                        </p>

                        <a href="forgot-password.php">
                            Forgot Password?
                        </a>

                    </div>


                </div>

            </div>


        </div>

    </div>

</div>


</body>

</html>