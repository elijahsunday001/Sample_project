<?php

/*
|--------------------------------------------------------------------------
| Escape output
|--------------------------------------------------------------------------
*/

function e($value)
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

function redirect($url)
{
    header("Location: " . $url);
    exit;
}


/*
|--------------------------------------------------------------------------
| Format money
|--------------------------------------------------------------------------
*/

function formatMoney($amount, $currency = "NGN")
{
    return $currency . " " . number_format(
        (float)$amount,
        2
    );
}


/*
|--------------------------------------------------------------------------
| Generate transaction reference
|--------------------------------------------------------------------------
*/

function generateTransactionReference()
{
    return "TXN-" .
        date("YmdHis") .
        "-" .
        strtoupper(
            substr(
                bin2hex(random_bytes(4)),
                0,
                8
            )
        );
}


/*
|--------------------------------------------------------------------------
| Generate account number
|--------------------------------------------------------------------------
*/

function generateAccountNumber($pdo)
{
    do {

        $accountNumber =
            "10" .
            random_int(
                100000000,
                999999999
            );

        $stmt = $pdo->prepare("
            SELECT id
            FROM accounts
            WHERE account_number = ?
            LIMIT 1
        ");

        $stmt->execute([
            $accountNumber
        ]);

    } while ($stmt->fetch());

    return $accountNumber;
}