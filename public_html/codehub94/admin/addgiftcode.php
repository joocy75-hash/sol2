<?php
session_start();
if (empty($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}

include("conn.php");

function generateRandomSerial($length = 32) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $randomString;
}

// Normal Gift Code Generator
if (!empty($_POST['maxserials']) && !empty($_POST['maxusers']) && !empty($_POST['price']) && isset($_POST['remark'])) {
    $maxserials = (int)mysqli_real_escape_string($conn, $_POST['maxserials']);
    $maxusers = (int)mysqli_real_escape_string($conn, $_POST['maxusers']);
    $price = (float)mysqli_real_escape_string($conn, $_POST['price']);
    $remark = mysqli_real_escape_string($conn, $_POST['remark']);

    if ($maxserials > 50) {
        echo '<script>alert("Maximum 50 codes allowed at a time."); window.history.back();</script>';
        exit;
    }

    $generatedSerials = [];
    $createdate = date("Y-m-d H:i:s");
    foreach (range(1, $maxserials) as $i) {
        $serial = generateRandomSerial();
        $generatedSerials[] = $serial;
        mysqli_query($conn, "INSERT INTO hodike_nirvahaka (enserie, utilisateurmax, prix, nombredutilisateurs, creerunrendezvous, shonu, remark) 
                             VALUES ('$serial', '$maxusers', '$price', 0, '$createdate', 1, '$remark')");
    }
    $generatedSerialsJson = json_encode($generatedSerials);
}

// Internal Gift Code Generator (no INT- prefix)
if (!empty($_POST['generate_internal'])) {
    $rechargeRequired = (float)mysqli_real_escape_string($conn, $_POST['recharge_required']);
    $maxserials = (int)mysqli_real_escape_string($conn, $_POST['internal_maxserials']);
    $maxusers = (int)mysqli_real_escape_string($conn, $_POST['internal_maxusers']);
    $price = (float)mysqli_real_escape_string($conn, $_POST['internal_price']);
    $remark = mysqli_real_escape_string($conn, $_POST['internal_remark']);

    if ($maxserials > 50) {
        echo '<script>alert("Maximum 50 internal codes allowed at a time."); window.history.back();</script>';
        exit;
    }

    $generatedInternalSerials = [];
    $createdate = date("Y-m-d H:i:s");
    foreach (range(1, $maxserials) as $i) {
        $serial = generateRandomSerial();
        $generatedInternalSerials[] = $serial;
        mysqli_query($conn, "INSERT INTO hodike_nirvahaka (enserie, utilisateurmax, prix, nombredutilisateurs, creerunrendezvous, shonu, remark, recharge_required) 
                             VALUES ('$serial', '$maxusers', '$price', 0, '$createdate', 1, '$remark', '$rechargeRequired')");
    }
    $generatedInternalSerialsJson = json_encode($generatedInternalSerials);
}

// Pagination
$limit = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$historyResult = mysqli_query($conn, "SELECT * FROM hodike_nirvahaka WHERE recharge_required IS NULL ORDER BY creerunrendezvous DESC LIMIT $limit OFFSET $offset");
$totalResult = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM hodike_nirvahaka WHERE recharge_required IS NULL"));
$totalPages = ceil($totalResult['total'] / $limit);

$internalCodes = mysqli_query($conn, "SELECT * FROM hodike_nirvahaka WHERE recharge_required IS NOT NULL ORDER BY creerunrendezvous DESC");

// Delete
if (!empty($_POST['delete_serial'])) {
    $serialToDelete = mysqli_real_escape_string($conn, $_POST['delete_serial']);
    mysqli_query($conn, "DELETE FROM hodike_nirvahaka WHERE enserie = '$serialToDelete'");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<?php include 'header.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Gift Code Generator</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="container mt-5">
    <h2>Normal Gift Code Generator</h2>
    <form method="post">
        <div class="row mb-3">
            <div class="col-md-3"><input name="maxserials" type="number" class="form-control" placeholder="Number of codes" required></div>
            <div class="col-md-3"><input name="maxusers" type="number" class="form-control" placeholder="Max users per code" required></div>
            <div class="col-md-3"><input name="price" type="number" class="form-control" placeholder="Price per code" required></div>
            <div class="col-md-3"><input name="remark" type="text" class="form-control" placeholder="Remark" required></div>
        </div>
        <button type="submit" class="btn btn-primary w-100">Generate Normal Codes</button>
    </form>

    <h2 class="mt-5">Internal Gift Code Generator</h2>
    <form method="post">
        <input type="hidden" name="generate_internal" value="1">
        <div class="row mb-3">
            <div class="col-md-3"><input name="recharge_required" type="number" class="form-control" placeholder="Recharge Required (৳)" required></div>
            <div class="col-md-3"><input name="internal_maxserials" type="number" class="form-control" placeholder="Number of codes" required></div>
            <div class="col-md-3"><input name="internal_maxusers" type="number" class="form-control" placeholder="Max users per code" required></div>
            <div class="col-md-3"><input name="internal_price" type="number" class="form-control" placeholder="Price per code" required></div>
        </div>
        <input name="internal_remark" type="text" class="form-control mb-3" placeholder="Remark" required>
        <button type="submit" class="btn btn-secondary w-100">Generate Internal Codes</button>
    </form>

    <h3 class="mt-5">Normal Gift Codes</h3>
    <table class="table table-bordered">
        <thead><tr><th>Serial</th><th>Max Users</th><th>Price</th><th>Created</th><th>Action</th></tr></thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($historyResult)): ?>
            <tr>
                <td><?= htmlspecialchars($row['enserie']) ?></td>
                <td><?= $row['utilisateurmax'] ?></td>
                <td>৳<?= $row['prix'] ?></td>
                <td><?= $row['creerunrendezvous'] ?></td>
                <td>
                    <form method="post"><input type="hidden" name="delete_serial" value="<?= $row['enserie'] ?>">
                        <button class="btn btn-danger btn-sm">Delete</button></form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <h3 class="mt-5">Internal Gift Codes</h3>
    <table class="table table-bordered">
        <thead><tr><th>Serial</th><th>Max Users</th><th>Recharge Required</th><th>Price</th><th>Created</th><th>Action</th></tr></thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($internalCodes)): ?>
            <tr>
                <td><?= htmlspecialchars($row['enserie']) ?></td>
                <td><?= $row['utilisateurmax'] ?></td>
                <td>৳<?= $row['recharge_required'] ?></td>
                <td>৳<?= $row['prix'] ?></td>
                <td><?= $row['creerunrendezvous'] ?></td>
                <td>
                    <form method="post"><input type="hidden" name="delete_serial" value="<?= $row['enserie'] ?>">
                        <button class="btn btn-danger btn-sm">Delete</button></form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
<?php if (isset($generatedSerialsJson)): ?>
    let generatedSerials = <?= $generatedSerialsJson ?>;
    alert("Generated Serial(s):\n" + generatedSerials.join("\n"));
<?php endif; ?>
<?php if (isset($generatedInternalSerialsJson)): ?>
    let internalSerials = <?= $generatedInternalSerialsJson ?>;
    alert("Generated Internal Serial(s):\n" + internalSerials.join("\n"));
<?php endif; ?>
</script>
</body>
</html>
