<?php

require_once "config/database.php";
require_once "includes/functions.php";

$message = "";
$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);


    if (!filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )) {

        $error =
            "Enter a valid email address.";

    } else {

        $stmt = $pdo->prepare(
            "SELECT id
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $stmt->execute([$email]);

        $user = $stmt->fetch();


        /*
         * For this demo, we display the reset
         * link directly instead of sending email.
         */

        if ($user) {

            $token =
                bin2hex(
                    random_bytes(32)
                );


            $expires =
                date(
                    "Y-m-d H:i:s",
                    time() + 3600
                );


            $stmt = $pdo->prepare(
                "UPDATE users
                 SET reset_token = ?,
                     reset_expires = ?
                 WHERE id = ?"
            );

            $stmt->execute([
                $token,
                $expires,
                $user["id"]
            ]);


            $reset_link =
                "reset-password.php?token="
                . urlencode($token);


            $message =
                "Password reset link generated.";

        } else {

            /*
             * Don't reveal whether an account exists
             * in a production system.
             */

            $message =
                "If the email exists, a reset link has been generated.";

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
        Forgot Password - SecureBank
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<div class="container">

    <div class="row justify-content-center">

        <div class="col-md-5">

            <div class="card shadow login-card">

                <div class="card-body p-4">

                    <h2 class="mb-4">
                        Forgot Password
                    </h2>


                    <?php if ($message): ?>

                        <div class="alert alert-success">

                            <?php echo e($message); ?>


                            <?php if (
                                isset($reset_link)
                            ): ?>

                                <hr>

                                <strong>
                                    Demo Reset Link:
                                </strong>

                                <br>

                                <a
                                    href="<?php echo e($reset_link); ?>"
                                >
                                    Reset Password
                                </a>

                            <?php endif; ?>

                        </div>

                    <?php endif; ?>


                    <?php if ($error): ?>

                        <div class="alert alert-danger">
                            <?php echo e($error); ?>
                        </div>

                    <?php endif; ?>


                    <form method="POST">

                        <div class="mb-4">

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


                        <button
                            class="btn btn-primary w-100"
                        >
                            Generate Reset Link
                        </button>

                    </form>


                    <p class="text-center mt-4">

                        <a href="login.php">
                            ← Back to Login
                        </a>

                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>