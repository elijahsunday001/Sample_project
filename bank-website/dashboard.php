<?php

require_once "includes/auth.php";
require_once "config/database.php";
require_once "includes/functions.php";


$userStmt = $pdo->prepare("
    SELECT
        u.id,
        u.first_name,
        u.last_name,
        u.email,
        u.phone,
        u.role,
        u.status,

        a.id AS account_id,
        a.account_number,
        a.account_type,
        a.balance,
        a.currency,
        a.account_status

    FROM users u

    LEFT JOIN accounts a
        ON a.user_id = u.id

    WHERE u.id = ?

    LIMIT 1
");


$userStmt->execute([
    $_SESSION["user_id"]
]);


$user = $userStmt->fetch();


if (!$user) {

    session_destroy();

    redirect("login.php");

}


$transactionStmt = $pdo->prepare("
    SELECT *
    FROM transactions
    WHERE account_id = ?
    ORDER BY created_at DESC
    LIMIT 5
");


$transactionStmt->execute([
    $user["account_id"]
]);


$recent_transactions =
    $transactionStmt->fetchAll();


$totalDepositStmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0)
    FROM transactions
    WHERE account_id = ?
    AND transaction_type = 'deposit'
    AND status = 'completed'
");


$totalDepositStmt->execute([
    $user["account_id"]
]);


$totalDeposits =
    $totalDepositStmt->fetchColumn();


$totalWithdrawalStmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0)
    FROM transactions
    WHERE account_id = ?
    AND transaction_type = 'withdrawal'
    AND status = 'completed'
");


$totalWithdrawalStmt->execute([
    $user["account_id"]
]);


$totalWithdrawals =
    $totalWithdrawalStmt->fetchColumn();


$totalTransferStmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0)
    FROM transactions
    WHERE account_id = ?
    AND transaction_type = 'transfer'
    AND status = 'completed'
");


$totalTransferStmt->execute([
    $user["account_id"]
]);


$totalTransfers =
    $totalTransferStmt->fetchColumn();


require_once "includes/header.php";

?>


<div class="container py-5">


    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>
                Welcome,
                <?php echo e($user["first_name"]); ?>
            </h2>

            <p class="text-muted mb-0">

                Account Number:

                <strong>
                    <?php echo e($user["account_number"]); ?>
                </strong>

            </p>

        </div>

    </div>


    <div class="row g-4 mb-4">


        <div class="col-lg-6">

            <div class="card balance-card h-100">

                <div class="card-body p-4">

                    <p>
                        Available Balance
                    </p>

                    <h1 class="display-5">

                        <?php

                        echo formatMoney(
                            $user["balance"],
                            $user["currency"]
                        );

                        ?>

                    </h1>

                    <p class="mb-0">

                        <?php

                        echo e(
                            ucfirst(
                                $user["account_type"]
                            )
                        );

                        ?>

                        Account

                    </p>

                </div>

            </div>

        </div>


        <div class="col-lg-2">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <small class="text-muted">
                        Deposits
                    </small>

                    <h5 class="mt-2">

                        <?php

                        echo formatMoney(
                            $totalDeposits,
                            $user["currency"]
                        );

                        ?>

                    </h5>

                </div>

            </div>

        </div>


        <div class="col-lg-2">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <small class="text-muted">
                        Withdrawals
                    </small>

                    <h5 class="mt-2">

                        <?php

                        echo formatMoney(
                            $totalWithdrawals,
                            $user["currency"]
                        );

                        ?>

                    </h5>

                </div>

            </div>

        </div>


        <div class="col-lg-2">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <small class="text-muted">
                        Transfers
                    </small>

                    <h5 class="mt-2">

                        <?php

                        echo formatMoney(
                            $totalTransfers,
                            $user["currency"]
                        );

                        ?>

                    </h5>

                </div>

            </div>

        </div>

    </div>


    <div class="row g-3 mb-5">


        <div class="col-md-4">

            <a
                href="deposit.php"
                class="text-decoration-none"
            >

                <div class="card action-card">

                    <div class="card-body p-4">

                        <h4>
                            💰 Deposit
                        </h4>

                        <p class="text-muted mb-0">
                            Add money to your account.
                        </p>

                    </div>

                </div>

            </a>

        </div>


        <div class="col-md-4">

            <a
                href="withdraw.php"
                class="text-decoration-none"
            >

                <div class="card action-card">

                    <div class="card-body p-4">

                        <h4>
                            💸 Withdraw
                        </h4>

                        <p class="text-muted mb-0">
                            Withdraw money from your account.
                        </p>

                    </div>

                </div>

            </a>

        </div>


        <div class="col-md-4">

            <a
                href="transfer.php"
                class="text-decoration-none"
            >

                <div class="card action-card">

                    <div class="card-body p-4">

                        <h4>
                            🔄 Transfer
                        </h4>

                        <p class="text-muted mb-0">
                            Send money to another account.
                        </p>

                    </div>

                </div>

            </a>

        </div>


    </div>


    <div class="card shadow-sm">

        <div class="card-body">


            <div class="d-flex justify-content-between">

                <h4>
                    Recent Transactions
                </h4>

                <a href="transactions.php">
                    View All
                </a>

            </div>


            <hr>


            <div class="table-responsive">

                <table class="table table-hover">

                    <thead>

                        <tr>

                            <th>
                                Reference
                            </th>

                            <th>
                                Type
                            </th>

                            <th>
                                Amount
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Date
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (!$recent_transactions): ?>

                        <tr>

                            <td
                                colspan="5"
                                class="text-center text-muted"
                            >
                                No transactions yet.
                            </td>

                        </tr>

                    <?php else: ?>


                        <?php foreach (
                            $recent_transactions
                            as $transaction
                        ): ?>


                            <tr>

                                <td>

                                    <?php echo e(
                                        $transaction[
                                            "transaction_reference"
                                        ]
                                    ); ?>

                                </td>


                                <td>

                                    <?php echo e(
                                        ucwords(
                                            str_replace(
                                                "_",
                                                " ",
                                                $transaction[
                                                    "transaction_type"
                                                ]
                                            )
                                        )
                                    ); ?>

                                </td>


                                <td>

                                    <?php echo formatMoney(
                                        $transaction["amount"],
                                        $user["currency"]
                                    ); ?>

                                </td>


                                <td>

                                    <span class="badge bg-success">

                                        <?php echo e(
                                            ucfirst(
                                                $transaction["status"]
                                            )
                                        ); ?>

                                    </span>

                                </td>


                                <td>

                                    <?php echo date(
                                        "d M Y H:i",
                                        strtotime(
                                            $transaction["created_at"]
                                        )
                                    ); ?>

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


<?php

require_once "includes/footer.php";

?>