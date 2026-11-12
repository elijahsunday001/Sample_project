<?php

require_once "config/database.php";
require_once "includes/functions.php";


$token =
    $_GET["token"]
    ?? $_POST["token"]
    ?? "";

$message = "";
$error = "";


if (empty($token)) {

    die("Invalid password reset request.");

}


$stmt = $pdo->prepare(
    "SELECT id
     FROM users
     WHERE reset_token = ?
     AND reset_expires > NOW()
     LIMIT 1"
);

$stmt->execute([$token]);

$user = $stmt->fetch();


if (!$user) {

    die(
        "This password reset link is invalid or has expired."
    );

}


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $password =
        $_POST["password"];

    $confirm =
        $_POST["confirm_password"];


    if (strlen($password) < 8) {

        $error =
            "Password must contain at least 8 characters.";

    } elseif ($password !== $confirm) {

        $error =
            "Passwords do not match.";

    } else {

        $hashed =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );


        $stmt = $pdo->prepare(
            "UPDATE users
             SET password = ?,
                 reset_token = NULL,
                 reset_expires = NULL
             WHERE id = ?"
        );


        $stmt->execute([
            $hashed,
            $user["id"]
        ]);


        $message =
            "Password reset successfully.";

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
        Reset Password - SecureBank
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
                        Reset Password
                    </h2>


                    <?php if ($message): ?>

                        <div class="alert alert-success">

                            <?php echo e($message); ?>

                            <hr>

                            <a href="login.php">
                                Login to your account
                            </a>

                        </div>

                    <?php else: ?>


                        <?php if ($error): ?>

                            <div class="alert alert-danger">

                                <?php echo e($error); ?>

                            </div>

                        <?php endif; ?>


                        <form method="POST">

                            <input
                                type="hidden"
                                name="token"
                                value="<?php echo e($token); ?>"
                            >


                            <div class="mb-3">

                                <label class="form-label">
                                    New Password
                                </label>

                                <input
                                    type="password"
                                    name="password"
                                    class="form-control"
                                    minlength="8"
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
                                    minlength="8"
                                    required
                                >

                            </div>


                            <button
                                class="btn btn-primary w-100"
                            >
                                Reset Password
                            </button>

                        </form>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>