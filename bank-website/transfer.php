<?php

require_once "includes/auth.php";
require_once "config/database.php";
require_once "includes/functions.php";

$error = "";
$success = "";


// Get logged-in user's account
$stmt = $pdo->prepare("
    SELECT *
    FROM accounts
    WHERE user_id = ?
    LIMIT 1
");

$stmt->execute([$_SESSION["user_id"]]);
$senderAccount = $stmt->fetch();


if (!$senderAccount) {
    die("Your bank account could not be found.");
}


// Process transfer
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $recipientAccountNumber = trim($_POST["recipient_account"] ?? "");
    $amount = trim($_POST["amount"] ?? "");
    $description = trim($_POST["description"] ?? "");


    // Validate recipient account
    if (empty($recipientAccountNumber)) {
        $error = "Please enter the recipient account number.";
    }

    // Validate amount
    elseif (!is_numeric($amount) || $amount <= 0) {
        $error = "Please enter a valid transfer amount.";
    }

    elseif ($amount > 100000000) {
        $error = "Transfer amount is too large.";
    }

    // Prevent transferring to same account
    elseif ($recipientAccountNumber === $senderAccount["account_number"]) {
        $error = "You cannot transfer money to your own account.";
    }

    else {

        try {

            $pdo->beginTransaction();


            /*
             * Lock sender account
             */
            $senderStmt = $pdo->prepare("
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

            $senderStmt->execute([
                $senderAccount["id"]
            ]);

            $sender = $senderStmt->fetch();


            if (!$sender) {
                throw new Exception("Sender account could not be found.");
            }


            // Check sender account status
            if ($sender["account_status"] !== "active") {
                throw new Exception("Your account is not active.");
            }


            /*
             * Lock recipient account
             */
            $recipientStmt = $pdo->prepare("
                SELECT
                    id,
                    account_number,
                    balance,
                    currency,
                    account_status
                FROM accounts
                WHERE account_number = ?
                FOR UPDATE
            ");

            $recipientStmt->execute([
                $recipientAccountNumber
            ]);

            $recipient = $recipientStmt->fetch();


            if (!$recipient) {
                throw new Exception("Recipient account was not found.");
            }


            // Check recipient account status
            if ($recipient["account_status"] !== "active") {
                throw new Exception("Recipient account is not active.");
            }


            // Make sure currencies match
            if ($sender["currency"] !== $recipient["currency"]) {
                throw new Exception(
                    "Sender and recipient accounts must use the same currency."
                );
            }


            // Convert amount to float
            $transferAmount = round((float)$amount, 2);

            $senderBalance = round((float)$sender["balance"], 2);


            // Check sufficient balance
            if ($transferAmount > $senderBalance) {
                throw new Exception("Insufficient funds for this transfer.");
            }


            /*
             * Generate two references.
             * One for the debit and one for the credit.
             */
            $senderReference = generateTransactionReference();
            $recipientReference = generateTransactionReference();


            /*
             * Deduct money from sender
             */
            $updateSender = $pdo->prepare("
                UPDATE accounts
                SET balance = balance - ?
                WHERE id = ?
            ");

            $updateSender->execute([
                $transferAmount,
                $sender["id"]
            ]);


            /*
             * Add money to recipient
             */
            $updateRecipient = $pdo->prepare("
                UPDATE accounts
                SET balance = balance + ?
                WHERE id = ?
            ");

            $updateRecipient->execute([
                $transferAmount,
                $recipient["id"]
            ]);


            /*
             * Create sender transaction
             */
            $senderDescription = !empty($description)
                ? $description
                : "Transfer to account " . $recipient["account_number"];


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
                    'transfer',
                    ?,
                    'completed',
                    ?,
                    NOW()
                )
            ");

            $transactionStmt->execute([
                $sender["id"],
                $senderReference,
                $transferAmount,
                $senderDescription
            ]);


            /*
             * Create recipient transaction
             */
            $recipientDescription =
                "Transfer received from account " .
                $sender["account_number"];


            $recipientTransactionStmt = $pdo->prepare("
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
                    'transfer',
                    ?,
                    'completed',
                    ?,
                    NOW()
                )
            ");

            $recipientTransactionStmt->execute([
                $recipient["id"],
                $recipientReference,
                $transferAmount,
                $recipientDescription
            ]);


            /*
             * Commit everything
             */
            $pdo->commit();


            // Refresh sender account
            $stmt = $pdo->prepare("
                SELECT *
                FROM accounts
                WHERE user_id = ?
                LIMIT 1
            ");

            $stmt->execute([
                $_SESSION["user_id"]
            ]);

            $senderAccount = $stmt->fetch();


            $success =
                "Transfer successful. $" .
                number_format($transferAmount, 2) .
                " has been transferred to account " .
                $recipient["account_number"] . ".";


        } catch (Exception $e) {

            // Rollback if something failed
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = "Unable to process the transfer.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Transfer Money - ElleyPAY</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="assets/css/style.css">

</head>


<body>

<?php require_once "includes/header.php"; ?>


<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-md-7 col-lg-6">

            <div class="card shadow-sm border-0">

                <div class="card-body p-4">

                    <h3 class="mb-2">
                        Transfer Money
                    </h3>

                    <p class="text-muted">
                        Send money securely to another ElleyPAY account.
                    </p>


                    <!-- Current Balance -->

                    <div class="alert alert-light border">

                        <strong>
                            Available Balance:
                        </strong>

                        <span class="float-end">

                            <?php
                            echo formatMoney(
                                $senderAccount["balance"],
                                $senderAccount["currency"]
                            );
                            ?>

                        </span>

                    </div>


                    <!-- Error -->

                    <?php if (!empty($error)): ?>

                        <div class="alert alert-danger">
                            <?php echo e($error); ?>
                        </div>

                    <?php endif; ?>


                    <!-- Success -->

                    <?php if (!empty($success)): ?>

                        <div class="alert alert-success">

                            <?php echo e($success); ?>

                        </div>

                    <?php endif; ?>


                    <!-- Transfer Form -->

                    <form method="POST">


                        <!-- Recipient Account -->

                        <div class="mb-3">

                            <label class="form-label">
                                Recipient Account Number
                            </label>

                            <input
                                type="text"
                                name="recipient_account"
                                class="form-control"
                                placeholder="Enter recipient account number"
                                required
                            >

                        </div>


                        <!-- Amount -->

                        <div class="mb-3">

                            <label class="form-label">
                                Amount
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    $
                                </span>

                                <input
                                    type="number"
                                    name="amount"
                                    class="form-control"
                                    step="0.01"
                                    min="0.01"
                                    placeholder="0.00"
                                    required
                                >

                            </div>

                        </div>


                        <!-- Description -->

                        <div class="mb-3">

                            <label class="form-label">
                                Description
                                <span class="text-muted">
                                    (Optional)
                                </span>
                            </label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="3"
                                placeholder="What is this transfer for?"
                            ></textarea>

                        </div>


                        <!-- Submit -->

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >

                            Transfer Money

                        </button>


                    </form>

                </div>

            </div>

        </div>

    </div>

</div>


<?php require_once "includes/footer.php"; ?>

</body>

</html>