<?php

require_once "includes/auth.php";
require_once "config/database.php";
require_once "includes/functions.php";


/*
|--------------------------------------------------------------------------
| Get user's account
|--------------------------------------------------------------------------
*/

$accountStmt = $pdo->prepare("
    SELECT
        id,
        account_number,
        balance,
        currency
    FROM accounts
    WHERE user_id = ?
    LIMIT 1
");

$accountStmt->execute([
    $_SESSION["user_id"]
]);

$account = $accountStmt->fetch();


if (!$account) {

    die("Bank account not found.");

}


/*
|--------------------------------------------------------------------------
| Get all transactions
|--------------------------------------------------------------------------
*/

$transactionStmt = $pdo->prepare("
    SELECT
        id,
        transaction_reference,
        transaction_type,
        amount,
        status,
        description,
        created_at
    FROM transactions
    WHERE account_id = ?
    ORDER BY created_at DESC
");

$transactionStmt->execute([
    $account["id"]
]);

$transactions = $transactionStmt->fetchAll();


require_once "includes/header.php";

?>


<div class="container py-5">


    <!-- PAGE HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>
                Transaction History
            </h2>

            <p class="text-muted mb-0">

                Account:

                <strong>
                    <?php echo e(
                        $account["account_number"]
                    ); ?>
                </strong>

            </p>

        </div>


        <div>

            <a
                href="dashboard.php"
                class="btn btn-outline-primary"
            >
                ← Dashboard
            </a>

        </div>

    </div>


    <!-- BALANCE -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <div class="row align-items-center">

                <div class="col-md-6">

                    <small class="text-muted">
                        Available Balance
                    </small>

                    <h3 class="mb-0">

                        <?php

                        echo formatMoney(
                            $account["balance"],
                            $account["currency"]
                        );

                        ?>

                    </h3>

                </div>


                <div class="col-md-6 text-md-end">

                    <a
                        href="deposit.php"
                        class="btn btn-primary"
                    >
                        💰 Deposit
                    </a>

                    <a
                        href="withdraw.php"
                        class="btn btn-outline-danger"
                    >
                        💸 Withdraw
                    </a>

                </div>

            </div>

        </div>

    </div>


    <!-- TRANSACTIONS -->

    <div class="card shadow-sm border-0">

        <div class="card-body">


            <h4 class="mb-4">
                All Transactions
            </h4>


            <?php if (!$transactions): ?>


                <div class="text-center py-5">

                    <div
                        class="display-4 mb-3"
                    >
                        📭
                    </div>

                    <h5>
                        No Transactions Yet
                    </h5>

                    <p class="text-muted">
                        Your transactions will appear here.
                    </p>


                    <a
                        href="deposit.php"
                        class="btn btn-primary"
                    >
                        Make Your First Deposit
                    </a>

                </div>


            <?php else: ?>


                <div class="table-responsive">

                    <table
                        class="table table-hover align-middle"
                    >

                        <thead>

                            <tr>

                                <th>
                                    Reference
                                </th>

                                <th>
                                    Type
                                </th>

                                <th>
                                    Description
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


                        <?php foreach (
                            $transactions
                            as $transaction
                        ): ?>


                            <?php

                            /*
                            |--------------------------------------------------------------------------
                            | Determine transaction type
                            |--------------------------------------------------------------------------
                            */

                            $type =
                                $transaction[
                                    "transaction_type"
                                ];


                            /*
                            |--------------------------------------------------------------------------
                            | Badge
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $type === "deposit"
                            ) {

                                $typeClass =
                                    "bg-success";

                            } elseif (
                                $type === "withdrawal"
                            ) {

                                $typeClass =
                                    "bg-danger";

                            } else {

                                $typeClass =
                                    "bg-primary";

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Amount styling
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $type === "deposit"
                            ) {

                                $amountClass =
                                    "text-success";

                                $prefix = "+";

                            } elseif (
                                $type === "withdrawal"
                                ||
                                $type === "transfer"
                            ) {

                                $amountClass =
                                    "text-danger";

                                $prefix = "-";

                            } else {

                                $amountClass =
                                    "";

                                $prefix = "";

                            }

                            ?>


                            <tr>


                                <!-- REFERENCE -->

                                <td>

                                    <strong>

                                        <?php echo e(
                                            $transaction[
                                                "transaction_reference"
                                            ]
                                        ); ?>

                                    </strong>

                                </td>


                                <!-- TYPE -->

                                <td>

                                    <span
                                        class="badge <?php echo $typeClass; ?>"
                                    >

                                        <?php

                                        echo e(

                                            ucwords(

                                                str_replace(
                                                    "_",
                                                    " ",
                                                    $type
                                                )

                                            )

                                        );

                                        ?>

                                    </span>

                                </td>


                                <!-- DESCRIPTION -->

                                <td>

                                    <?php

                                    echo e(
                                        $transaction[
                                            "description"
                                        ]
                                        ?: "Transaction"
                                    );

                                    ?>

                                </td>


                                <!-- AMOUNT -->

                                <td>

                                    <strong
                                        class="<?php echo $amountClass; ?>"
                                    >

                                        <?php

                                        echo $prefix;

                                        echo formatMoney(
                                            $transaction[
                                                "amount"
                                            ],
                                            $account[
                                                "currency"
                                            ]
                                        );

                                        ?>

                                    </strong>

                                </td>


                                <!-- STATUS -->

                                <td>


                                    <?php

                                    $status =
                                        $transaction[
                                            "status"
                                        ];


                                    if (
                                        $status === "completed"
                                    ) {

                                        $statusClass =
                                            "bg-success";

                                    } elseif (
                                        $status === "pending"
                                    ) {

                                        $statusClass =
                                            "bg-warning text-dark";

                                    } else {

                                        $statusClass =
                                            "bg-danger";

                                    }

                                    ?>


                                    <span
                                        class="badge <?php echo $statusClass; ?>"
                                    >

                                        <?php

                                        echo e(
                                            ucfirst(
                                                $status
                                            )
                                        );

                                        ?>

                                    </span>

                                </td>


                                <!-- DATE -->

                                <td>

                                    <?php

                                    echo date(
                                        "d M Y H:i",
                                        strtotime(
                                            $transaction[
                                                "created_at"
                                            ]
                                        )
                                    );

                                    ?>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                        </tbody>

                    </table>

                </div>


            <?php endif; ?>


        </div>

    </div>


</div>


<?php

require_once "includes/footer.php";

?>