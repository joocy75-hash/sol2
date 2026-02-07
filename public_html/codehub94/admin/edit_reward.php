<?php
include("conn.php");
if (!isset($_GET['id'])) {
    die("Reward ID not provided.");
}

$id = $_GET['id'];
$reward = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM signin_recharge_rewards WHERE id='$id'"));

if (isset($_POST['update'])) {
    $day = $_POST['day'];
    $amount = $_POST['amount'];
    $bonus = $_POST['bonus'];
    $status = $_POST['status'];

    mysqli_query($conn, "UPDATE signin_recharge_rewards SET day='$day', amount='$amount', bonus='$bonus', status='$status' WHERE id='$id'");
    echo "<script>alert('Reward updated successfully!'); location.href='manage_rewards.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Reward</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4 text-center">✏️ Edit Sign-In Reward</h2>

    <div class="card">
        <div class="card-body">
            <form method="post">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Day</label>
                        <input type="number" name="day" class="form-control" value="<?php echo $reward['day']; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control" value="<?php echo $reward['amount']; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bonus</label>
                        <input type="number" step="0.01" name="bonus" class="form-control" value="<?php echo $reward['bonus']; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="1" <?php if ($reward['status']) echo 'selected'; ?>>Active</option>
                            <option value="0" <?php if (!$reward['status']) echo 'selected'; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" name="update" class="btn btn-success">Update Reward</button>
                    <a href="manage_rewards.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
