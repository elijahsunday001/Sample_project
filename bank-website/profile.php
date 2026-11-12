<?php

require_once "includes/auth.php";
require_once "config/database.php";
require_once "includes/functions.php";


$message = "";
$error = "";


$stmt = $pdo->prepare(
    "SELECT *
     FROM users
     WHERE id = ?"
);

$stmt->execute([
    $_SESSION["user_id"]
]);

$user = $stmt->fetch();


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $first_name =
        trim($_POST["first_name"]);

    $last_name =
        trim($_POST["last_name"]);

    $phone =
        trim($_POST["phone"]);


    if (
        empty($first_name) ||
        empty($last_name)
    ) {

        $error =
            "First name and last name are required.";

    } else {

        $stmt = $pdo->prepare(
            "UPDATE users
             SET first_name = ?,
                 last_name = ?,
                 phone = ?
             WHERE id = ?"
        );

        $stmt->execute([
            $first_name,
            $last_name,
            $phone,
            $_SESSION["user_id"]
        ]);


        $_SESSION["user_name"] =
            $first_name . " " . $last_name;


        $message =
            "Profile updated successfully.";


        $stmt = $pdo->prepare(
            "SELECT *
             FROM users
             WHERE id = ?"
        );

        $stmt->execute([
            $_SESSION["user_id"]
        ]);

        $user = $stmt->fetch();

    }
}


require_once "includes/header.php";

?>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-md-7">

            <div class="card shadow">

                <div class="card-body p-4">

                    <h2 class="mb-4">
                        Account Profile
                    </h2>


                    <?php if ($message): ?>

                        <div class="alert alert-success">
                            <?php echo e($message); ?>
                        </div>

                    <?php endif; ?>


                    <?php if ($error): ?>

                        <div class="alert alert-danger">
                            <?php echo e($error); ?>
                        </div>

                    <?php endif; ?>


                    <div class="mb-4">

                        <label class="form-label">
                            Account Number
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?php echo e($user["account_number"]); ?>"
                            readonly
                        >

                    </div>


                    <div class="mb-4">

                        <label class="form-label">
                            Email Address
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            value="<?php echo e($user["email"]); ?>"
                            readonly
                        >

                    </div>


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
                                    value="<?php echo e($user["first_name"]); ?>"
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
                                    value="<?php echo e($user["last_name"]); ?>"
                                    required
                                >

                            </div>

                        </div>


                        <div class="mb-4">

                            <label class="form-label">
                                Phone Number
                            </label>

                            <input
                                type="text"
                                name="phone"
                                class="form-control"
                                value="<?php echo e($user["phone"]); ?>"
                            >

                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Update Profile
                        </button>


                        <a
                            href="change-password.php"
                            class="btn btn-outline-secondary ms-2"
                        >
                            Change Password
                        </a>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>


<?php require_once "includes/footer.php"; ?>