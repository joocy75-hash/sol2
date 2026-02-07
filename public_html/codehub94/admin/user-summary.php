<?php
include("conn.php"); // your DB connection


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function fetchUserDownlineData($userId) {
    global $conn;
    
    $levels = [
        'code' => 1,
        'code1' => 2,
        'code2' => 3,
        'code3' => 4,
        'code4' => 5,
        'code5' => 6,
    ];

    $summary = [];

    foreach ($levels as $codeColumn => $level) {
        $sql = "SELECT u.id, u.mobile, u.owncode,
                (SELECT COUNT(*) FROM thevani t WHERE t.uid = u.id) as recharge_count,
                (SELECT IFNULL(SUM(t.amount), 0) FROM thevani t WHERE t.uid = u.id) as total_recharge,
                (SELECT IFNULL(MIN(t.amount), 0) FROM thevani t WHERE t.uid = u.id) as first_recharge,
                (SELECT COUNT(*) FROM bajikattuttate b WHERE b.uid = u.id) as bet_count,
                (SELECT IFNULL(SUM(b.amount), 0) FROM bajikattuttate b WHERE b.uid = u.id) as total_bet,
                (SELECT IFNULL(SUM(v.amount), 0) FROM vyavahara v WHERE v.uid = u.id) as rebate_amount
                FROM shonu_subjects u WHERE u.$codeColumn = ?";

        $sql = "SELECT id, mobile, owncode FROM shonu_subjects WHERE $codeColumn = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error); // Will show you what's wrong in the SQL
}
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $summary[$level] = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
    return $summary;
}

$data = [];
if (isset($_POST['userid'])) {
    $userid = trim($_POST['userid']);
    $data = fetchUserDownlineData($userid);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Downline Summary</title>
    <style>
        body { font-family: Arial; background: #f5f6fa; margin: 0; padding: 0; }
        .container { width: 90%; margin: auto; padding: 30px; }
        .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table th, table td { border: 1px solid #ddd; padding: 10px; text-align: center; font-size: 14px; }
        table th { background-color: #f0f0f0; }
        h2 { margin-bottom: 5px; }
        input[type="text"] { padding: 10px; width: 250px; border-radius: 5px; border: 1px solid #ccc; }
        button { padding: 10px 20px; background: #0984e3; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #74b9ff; }
    </style>
</head>
<body>
    <div class="container">
        <form method="POST">
            <input type="text" name="userid" placeholder="Enter User ID" required>
            <button type="submit">Search</button>
        </form>

        <?php if (!empty($data)) { ?>
            <?php foreach ($data as $level => $users) { ?>
                <div class="card">
                    <h2>Level <?php echo $level; ?> Downline</h2>
                    <?php if (count($users) > 0) { ?>
                        <table>
                            <tr>
                                <th>User ID</th>
                                <th>Mobile</th>
                                <th>Own Code</th>
                                <th>Deposit Count</th>
                                <th>Total Deposit</th>
                                <th>First Recharge</th>
                                <th>Bet Count</th>
                                <th>Total Bet</th>
                                <th>Rebate</th>
                            </tr>
                            <?php foreach ($users as $row) { ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo $row['mobile']; ?></td>
                                    <td><?php echo $row['owncode']; ?></td>
                                    <td><?php echo $row['recharge_count']; ?></td>
                                    <td><?php echo $row['total_recharge']; ?></td>
                                    <td><?php echo $row['first_recharge']; ?></td>
                                    <td><?php echo $row['bet_count']; ?></td>
                                    <td><?php echo $row['total_bet']; ?></td>
                                    <td><?php echo $row['rebate_amount']; ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    <?php } else { ?>
                        <p>No downline found for this level.</p>
                    <?php } ?>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
</body>
</html>
