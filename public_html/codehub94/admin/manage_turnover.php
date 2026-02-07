<?php
include("conn.php"); // your database config file

function getUserTurnover($conn, $userId) {
    // Get wallet balance
    $balRes = mysqli_query($conn, "SELECT motta FROM shonu_kaichila WHERE balakedara = '$userId'");
    $wallet = mysqli_fetch_assoc($balRes)['motta'] ?? 0;

    // Get mobile number
    $userRes = mysqli_query($conn, "SELECT mobile FROM shonu_subjects WHERE id = '$userId'");
    $mobile = mysqli_fetch_assoc($userRes)['mobile'] ?? 'N/A';

    // Recharge amount
    $recharge1 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(motta) AS total FROM thevani WHERE balakedara = '$userId' AND sthiti = '1'"))['total'] ?? 0;
    $recharge2 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(price) AS total FROM hodike_balakedara WHERE userkani = '$userId'"))['total'] ?? 0;
    $totalRecharge = $recharge1 + $recharge2;

    // Total Bet
    $tables = [
        'bajikattuttate_trx', 'bajikattuttate_trx3', 'bajikattuttate_trx5', 'bajikattuttate_trx10',
        'bajikattuttate', 'bajikattuttate_drei', 'bajikattuttate_funf', 'bajikattuttate_zehn',
        'bajikattuttate_kemuru', 'bajikattuttate_kemuru_drei', 'bajikattuttate_kemuru_funf', 'bajikattuttate_kemuru_zehn',
        'bajikattuttate_aidudi', 'bajikattuttate_aidudi_drei', 'bajikattuttate_aidudi_funf', 'bajikattuttate_aidudi_zehn'
    ];

    $totalBet = 0;
    foreach ($tables as $table) {
        $res = mysqli_query($conn, "SELECT SUM(ketebida) AS total FROM $table WHERE byabaharkarta = '$userId'");
        $total = mysqli_fetch_assoc($res)['total'] ?? 0;
        $totalBet += $total;
    }

    // Eligibility
    $canWithdraw = ($totalBet >= $totalRecharge) ? $wallet : 0;
    $needMoreBet = ($totalBet >= $totalRecharge) ? 0 : $totalRecharge - $totalBet;

    // Withdrawal range %
    $withdrawRange = ($totalRecharge > 0) ? round(($totalBet / $totalRecharge) * 100, 2) : 0;

    // Status
    $eligible = ($canWithdraw > 0) ? "✅ Eligible" : "❌ Not Eligible";

    return [
        "userId" => $userId,
        "mobile" => $mobile,
        "wallet" => $wallet,
        "recharge" => $totalRecharge,
        "totalBet" => $totalBet,
        "needMoreBet" => $needMoreBet,
        "withdrawRange" => $withdrawRange,
        "withdrawStatus" => $eligible,
        "canWithdraw" => $canWithdraw
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Turnover Report</title>
    <style>
        body { font-family: Arial; padding: 20px; background-color: #f8f9fa; }
        input[type="text"], input[type="submit"] {
            padding: 8px;
            margin: 5px;
            font-size: 16px;
        }
        table {
            border-collapse: collapse;
            margin-top: 20px;
            width: 100%;
            background: #fff;
            box-shadow: 0 0 10px #ccc;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        h2 {
            color: #333;
        }
    </style>
</head>
<body>

<h2>🔍 Search User Turnover Report</h2>

<form method="GET">
    <input type="text" name="user_id" placeholder="Enter User ID" required />
    <input type="submit" value="Search" />
</form>

<?php
if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $userId = $_GET['user_id'];
    $data = getUserTurnover($conn, $userId);

    echo "<table>
            <tr><th>User ID</th><td>{$data['userId']}</td></tr>
            <tr><th>Mobile Number</th><td>{$data['mobile']}</td></tr>
            <tr><th>Wallet Balance</th><td>৳" . number_format($data['wallet'], 2) . "</td></tr>
            <tr><th>Total Recharge</th><td>৳" . number_format($data['recharge'], 2) . "</td></tr>
            <tr><th>Total Bet</th><td>৳" . number_format($data['totalBet'], 2) . "</td></tr>
            <tr><th>Remaining to Bet</th><td>৳" . number_format($data['needMoreBet'], 2) . "</td></tr>
            <tr><th>Withdrawal Range</th><td>{$data['withdrawRange']}%</td></tr>
            <tr><th>Withdraw Status</th><td>{$data['withdrawStatus']}</td></tr>
            <tr><th>Can Withdraw Amount</th><td>৳" . number_format($data['canWithdraw'], 2) . "</td></tr>
          </table>";
}
?>

</body>
</html>
