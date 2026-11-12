<?php

require_once "includes/auth.php";
require_once "config/database.php";
require_once "includes/functions.php";

$error = "";
$success = "";


/*
|--------------------------------------------------------------------------
| Get logged-in user's account
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        account_number,
        account_type,
        balance,
        currency,
        account_status
    FROM accounts
    WHERE user_id = ?
    LIMIT 1
");

$stmt->execute([
    $_SESSION["user_id"]
]);

$account = $stmt->fetch();


if (!$account) {

    die("Bank account not found.");

}


/*
|--------------------------------------------------------------------------
| Check account status
|--------------------------------------------------------------------------
*/

if ($account["account_status"] !== "active") {

    die("Your bank account is not active.");

}


/*
|--------------------------------------------------------------------------
| Process Withdrawal
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $amount = trim($_POST["amount"] ?? "");


    /*
    |--------------------------------------------------------------------------
    | Validate amount
    |--------------------------------------------------------------------------
    */

    if ($amount === "") {

        $error = "Please enter a withdrawal amount.";

    } elseif (!is_numeric($amount)) {

        $error = "Please enter a valid amount.";

    } elseif ((float)$amount <= 0) {

        $error = "Withdrawal amount must be greater than zero.";

    } elseif ((float)$amount > 100000000) {

        $error = "Withdrawal amount is too large.";

    } else {

        $amount = round((float)$amount, 2);


        try {

            /*
            |--------------------------------------------------------------------------
            | Start database transaction
            |--------------------------------------------------------------------------
            */

            $pdo->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | Lock account
            |--------------------------------------------------------------------------
            */

            $accountStmt = $pdo->prepare("
                SELECT
                    id,
                    account_number,
                    balance,
                    currency,
                    account_status
                FROM accounts
                WHERE id = ?
                FOR UPDATE
            ");

            $accountStmt->execute([
                $account["id"]
            ]);

            $lockedAccount =
                $accountStmt->fetch();


            if (!$lockedAccount) {

                throw new Exception(
                    "Account could not be found."
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Check account status again
            |--------------------------------------------------------------------------
            */

            if (
                $lockedAccount["account_status"]
                !== "active"
            ) {

                throw new Exception(
                    "Your account is not active."
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Check available balance
            |--------------------------------------------------------------------------
            */

            $currentBalance =
                (float)$lockedAccount["balance"];


            if ($amount > $currentBalance) {

                throw new Exception(
                    "Insufficient funds."
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Calculate new balance
            |--------------------------------------------------------------------------
            */

            $newBalance =
                $currentBalance - $amount;


            /*
            |--------------------------------------------------------------------------
            | Update account balance
            |--------------------------------------------------------------------------
            */

            $updateStmt = $pdo->prepare("
                UPDATE accounts
                SET balance = ?
                WHERE id = ?
            ");

            $updateStmt->execute([
                $newBalance,
                $account["id"]
            ]);


            /*
            |--------------------------------------------------------------------------
            | Generate transaction reference
            |--------------------------------------------------------------------------
            */

            $reference =
                generateTransactionReference();


            /*
            |--------------------------------------------------------------------------
            | Create withdrawal transaction
            |--------------------------------------------------------------------------
            */

            $transactionStmt = $pdo->prepare("
                INSERT INTO transactions
                (
                    account_id,
                    transaction_reference,
                    transaction_type,
                    amount,
                    status,
                    description,
                    created_at
                )
                VALUES
                (
                    ?,
                    ?,
                    'withdrawal',
                    ?,
                    'completed',
                    ?,
                    NOW()
                )
            ");


            $transactionStmt->execute([
                $account["id"],
                $reference,
                $amount,
                "Withdrawal from bank account"
            ]);


            /*
            |--------------------------------------------------------------------------
            | Commit transaction
            |--------------------------------------------------------------------------
            */

            $pdo->commit();


            /*
            |--------------------------------------------------------------------------
            | Success message
            |--------------------------------------------------------------------------
            */

            $success =
                "Withdrawal successful. Transaction reference: "
                . $reference;


            /*
            |--------------------------------------------------------------------------
            | Refresh account information
            |--------------------------------------------------------------------------
            */

            $refreshStmt = $pdo->prepare("
                SELECT
                    id,
                    account_number,
                    account_type,
                    balance,
                    currency,
                    account_status
                FROM accounts
                WHERE id = ?
                LIMIT 1
            ");

            $refreshStmt->execute([
                $account["id"]
            ]);

            $account =
                $refreshStmt->fetch();


        } catch (Exception $e) {

            /*
            |--------------------------------------------------------------------------
            | Rollback
            |--------------------------------------------------------------------------
            */

            if ($pdo->inTransaction()) {

                $pdo->rollBack();

            }


            /*
            |--------------------------------------------------------------------------
            | Display user-friendly error
            |--------------------------------------------------------------------------
            */

            if (
                $e->getMessage()
                === "Insufficient funds."
            ) {

                $error =
                    "Insufficient funds. Your available balance is "
                    . formatMoney(
                        $account["balance"],
                        $account["currency"]
                    )
                    . ".";

            } else {

                $error =
                    "Unable to process the withdrawal.";

                /*
                |--------------------------------------------------------------------------
                | TEMPORARY DEBUGGING
                |--------------------------------------------------------------------------
                |
                | If the withdrawal still fails, temporarily replace
                | the line above with:
                |
                | $error = $e->getMessage();
                |
                */
            }
        }
    }
}


require_once "includes/header.php";

?>


<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-7">


            <div class="card shadow-sm border-0">

                <div class="card-body p-5">


                    <h2 class="mb-2">
                        Make a Withdrawal
                    </h2>

                    <p class="text-muted">
                        Withdraw money from your bank account.
                    </p>


                    <!-- ACCOUNT INFORMATION -->

                    <div class="alert alert-light border">

                        <div class="row">


                            <div class="col-md-6">

                                <small class="text-muted">
                                    Account Number
                                </small>

                                <div class="fw-bold">

                                    <?php echo e(
                                        $account["account_number"]
                                    ); ?>

                                </div>

                            </div>


                            <div class="col-md-6">

                                <small class="text-muted">
                                    Available Balance
                                </small>

                                <div class="fw-bold">

                                    <?php echo formatMoney(
                                        $account["balance"],
                                        $account["currency"]
                                    ); ?>

                                </div>

                            </div>


                        </div>

                    </div>


                    <!-- SUCCESS MESSAGE -->

                    <?php if ($success): ?>

                        <div class="alert alert-success">

                            <?php echo e($success); ?>

                        </div>

                    <?php endif; ?>


                    <!-- ERROR MESSAGE -->

                    <?php if ($error): ?>

                        <div class="alert alert-danger">

                            <?php echo e($error); ?>

                        </div>

                    <?php endif; ?>


                    <!-- WITHDRAWAL FORM -->

                    <form method="POST">


                        <div class="mb-4">

                            <label
                                for="amount"
                                class="form-label"
                            >
                                Withdrawal Amount
                            </label>


                            <div class="input-group">


                                <span class="input-group-text">

                                    <?php echo e(
                                        $account["currency"]
                                    ); ?>

                                </span>


                                <input
                                    type="number"
                                    name="amount"
                                    id="amount"
                                    class="form-control"
                                    placeholder="0.00"
                                    min="1"
                                    max="<?php echo e(
                                        $account["balance"]
                                    ); ?>"
                                    step="0.01"
                                    required
                                >


                            </div>


                            <div class="form-text">

                                Maximum available amount:

                                <?php echo formatMoney(
                                    $account["balance"],
                                    $account["currency"]
                                ); ?>

                            </div>


                        </div>


                        <button
                            type="submit"
                            class="btn btn-danger w-100"
                        >
                            💸 Withdraw Money
                        </button>


                    </form>


                    <div class="text-center mt-4">

                        <a href="dashboard.php">
                            ← Back to Dashboard
                        </a>

                    </div>


                </div>

            </div>


        </div>

    </div>

</div>


<?php

require_once "includes/footer.php";

?>
