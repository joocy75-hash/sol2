<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit();
}
include("conn.php");
date_default_timezone_set('Asia/Kolkata');

// Handle Approve/Reject
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['load_more'])) {
    $uid = mysqli_real_escape_string($conn, $_POST['uid']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $sid = mysqli_real_escape_string($conn, $_POST['sid']);
    
    if (isset($_POST['approve'])) {
        $wallet = mysqli_fetch_assoc(mysqli_query($conn, "SELECT motta FROM shonu_kaichila WHERE balakedara='$uid'"));
        $newBalance = intval($wallet['motta']) + intval($amount);
        mysqli_query($conn, "UPDATE shonu_kaichila SET motta='$newBalance' WHERE balakedara='$uid'");
        mysqli_query($conn, "UPDATE thevani SET sthiti='1' WHERE shonu='$sid'");
    } elseif (isset($_POST['reject'])) {
        mysqli_query($conn, "UPDATE thevani SET sthiti='2' WHERE shonu='$sid'");
    }
    
    if (!isset($_POST['load_more'])) {
        header("Location: deposit_update.php?success=1");
        exit();
    }
}

// AJAX Load More
if (isset($_POST['load_more'])) {
    $page = (int)$_POST['page'];
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
    $q = mysqli_query($conn, "SELECT * FROM thevani WHERE sthiti='1' ORDER BY shonu DESC LIMIT $limit OFFSET $offset");
    
    while ($r = mysqli_fetch_array($q)) {
        echo '<tr>
            <td><strong>' . htmlspecialchars($r['balakedara']) . '</strong></td>
            <td>' . htmlspecialchars($r['duravani']) . '</td>
            <td>' . htmlspecialchars($r['ullekha']) . '</td>
            <td><span class="text-success fw-bold">৳' . number_format($r['motta'], 2) . '</span></td>
            <td><code>' . htmlspecialchars($r['dharavahi']) . '</code></td>
            <td><small class="text-muted">' . htmlspecialchars($r['server_number'] ?: '—') . '</small></td>
            <td><span class="date-time">' . date('d-m-Y, h:i A', strtotime($r['dinankavannuracisi'])) . '</span></td>
            <td><strong>' . htmlspecialchars($r['mula'] ?: '—') . '</strong></td>
        </tr>';
    }
    exit();
}

// Counts
$today = date('Y-m-d');
$todayApproved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(motta) as total FROM thevani WHERE sthiti='1' AND DATE(dinankavannuracisi)='$today'"));
$todayApprovedAmount = $todayApproved['total'] ?: 0;

$totalApproved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(motta) as total FROM thevani WHERE sthiti='1'"));
$totalApprovedAmount = $totalApproved['total'] ?: 0;

$totalRejected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(motta) as total FROM thevani WHERE sthiti='2'"));
$totalRejectedAmount = $totalRejected['total'] ?: 0;

$pendingCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM thevani WHERE sthiti='0'"))['cnt'];
?>

<?php include 'header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit Update</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --blue: #1e40af;
            --blue-light: #3b82f6;
            --gray: #f8fafc;
        }
        * { box-sizing: border-box; }
        body {
            background: var(--gray);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden !important;
        }
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.2rem;
            width: 100%;
        }
        .page-title {
            font-size: 1.65rem;
            font-weight: 700;
            color: var(--blue);
            margin: 0 0 1.5rem 0;
            padding-bottom: 0.7rem;
            border-bottom: 5px solid var(--blue-light);
            display: inline-block;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.2rem;
            margin-bottom: 2rem;
        }
        .stat-box {
            background: white;
            padding: 1.4rem;
            border-radius: 20px;
            box-shadow: 0 10px 35px rgba(0,0,0,0.08);
            text-align: center;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 130px;
            transition: all 0.4s ease;
        }
        .stat-box:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 45px rgba(30,64,175,0.18);
        }
        .stat-label {
            font-size: 0.92rem;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 0.4rem;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--blue);
            line-height: 1.2;
            margin: 0;
        }
        .stat-amount { color: #dc2626; }
        .search-container {
            position: relative;
            max-width: 480px;
            margin: 0 0 2rem auto;
        }
        .search-box {
            width: 100%;
            padding: 0.9rem 1.6rem 0.9rem 4.2rem;
            font-size: 1.05rem;
            border: 2px solid #cbd5e1;
            border-radius: 50px;
            background: white;
            transition: border-color 0.3s ease;
            outline: none;
            font-weight: 500;
        }
        .search-box:focus {
            border-color: var(--blue-light);
        }
        .search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--blue);
            font-size: 1.3rem;
            pointer-events: none;
        }
        .table-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0,0,0,0.12);
            border: 1px solid #e2e8f0;
            margin-bottom: 2rem;
        }
        .section-title {
            background: linear-gradient(135deg, var(--blue), var(--blue-light));
            color: white;
            padding: 1.2rem 1.8rem;
            font-weight: 700;
            font-size: 1.15rem;
            margin: 0;
        }
        .completed-title {
            background: linear-gradient(135deg, #16a34a, #22c55e);
        }
        .table thead {
            background: #f8f9fa;
            color: #1e293b;
            font-weight: 600;
            font-size: 0.92rem;
        }
        .table tbody tr:hover {
            background: #f0f7ff !important;
        }
        .date-time {
            white-space: nowrap;
            display: block;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }
        .btn-approve, .btn-reject {
            min-width: 90px;
            padding: 0.55rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.88rem;
            transition: all 0.4s ease;
            text-align: center;
        }
        .btn-approve {
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: white;
        }
        .btn-reject {
            background: linear-gradient(135deg, #dc2626, #ef4444);
            color: white;
        }
        .btn-approve:hover, .btn-reject:hover {
            transform: translateY(-3px);
        }
        .loader {
            text-align: center;
            padding: 1.5rem;
            font-size: 1.1rem;
            color: var(--blue);
            font-weight: 600;
        }
        .no-more {
            text-align: center;
            padding: 1.5rem;
            color: #64748b;
            font-weight: 500;
        }
        .alert-success {
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: white;
            border: none;
            border-radius: 18px;
            padding: 1rem 2rem;
            font-weight: 600;
            box-shadow: 0 12px 35px rgba(22,163,74,0.35);
            margin-bottom: 2rem;
            animation: slideDown 0.6s ease;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-25px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 768px) {
            .main-container { padding: 1rem 0.8rem; }
            .page-title { font-size: 1.35rem; }
            .stats-grid { gap: 1rem; }
            .stat-box { padding: 1.2rem; height: 120px; }
            .stat-value { font-size: 1.85rem; }
            .search-container { max-width: 100%; }
            .search-box { padding: 0.8rem 1.4rem 0.8rem 4rem; font-size: 0.98rem; }
            .search-icon { left: 16px; font-size: 1.2rem; }
            .action-buttons { flex-direction: column; }
            .btn-approve, .btn-reject { width: 100%; }
        }
    </style>
</head>
<body>
<div class="main-container">
    <h2 class="page-title">Deposit Update</h2>

    <?php if (isset($_GET['success'])) { ?>
        <div class="alert alert-success" id="successAlert">
            Action performed successfully!
        </div>
    <?php } ?>

    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-label">Today's Approved</div>
            <div class="stat-value">৳<?= number_format($todayApprovedAmount, 0) ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Total Approved</div>
            <div class="stat-value">৳<?= number_format($totalApprovedAmount, 0) ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Total Rejected</div>
            <div class="stat-value stat-amount">৳<?= number_format($totalRejectedAmount, 0) ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Pending Requests</div>
            <div class="stat-value"><?= $pendingCount ?></div>
        </div>
    </div>

    <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="liveSearch" class="search-box" placeholder="Search Mobile / User ID / Server No. / Method..." autocomplete="off">
    </div>

    <!-- PENDING TABLE -->
    <div class="table-card">
        <h3 class="section-title">Pending Payment Update</h3>
        <div class="table-responsive">
            <table id="pendingTable" class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Mobile</th>
                        <th>Reference</th>
                        <th>Amount</th>
                        <th>Order ID</th>
                        <th>Server No.</th>
                        <th>Date & Time</th>
                        <th>Method</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $pending = mysqli_query($conn, "SELECT * FROM thevani WHERE sthiti='0' ORDER BY shonu DESC");
                    while ($row = mysqli_fetch_assoc($pending)) {
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($row['balakedara']) ?></strong></td>
                        <td><?= htmlspecialchars($row['duravani']) ?></td>
                        <td><?= htmlspecialchars($row['ullekha']) ?></td>
                        <td><span class="text-warning fw-bold">৳<?= number_format($row['motta'], 2) ?></span></td>
                        <td><code><?= htmlspecialchars($row['dharavahi']) ?></code></td>
                        <td><small class="text-muted"><?= htmlspecialchars($row['server_number'] ?: '—') ?></small></td>
                        <td><span class="date-time"><?= date('d-m-Y, h:i A', strtotime($row['dinankavannuracisi'])) ?></span></td>
                        <td><strong><?= htmlspecialchars($row['mula'] ?: '—') ?></strong></td>
                        <td>
                            <div class="action-buttons">
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="uid" value="<?= $row['balakedara'] ?>">
                                    <input type="hidden" name="amount" value="<?= $row['motta'] ?>">
                                    <input type="hidden" name="sid" value="<?= $row['shonu'] ?>">
                                    <button type="submit" name="approve" class="btn btn-approve btn-sm">Approve</button>
                                </form>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="uid" value="<?= $row['balakedara'] ?>">
                                    <input type="hidden" name="amount" value="<?= $row['motta'] ?>">
                                    <input type="hidden" name="sid" value="<?= $row['shonu'] ?>">
                                    <button type="submit" name="reject" class="btn btn-reject btn-sm">Reject</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- COMPLETED TABLE -->
    <div class="table-card">
        <h3 class="section-title completed-title">Completed Payment Records</h3>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Mobile</th>
                        <th>Reference</th>
                        <th>Amount</th>
                        <th>Order ID</th>
                        <th>Server No.</th>
                        <th>Date & Time</th>
                        <th>Method</th>
                    </tr>
                </thead>
                <tbody id="completedBody">
                    <?php
                    $first = mysqli_query($conn, "SELECT * FROM thevani WHERE sthiti='1' ORDER BY shonu DESC LIMIT 20");
                    while ($r = mysqli_fetch_array($first)) {
                        echo '<tr>
                            <td><strong>' . htmlspecialchars($r['balakedara']) . '</strong></td>
                            <td>' . htmlspecialchars($r['duravani']) . '</td>
                            <td>' . htmlspecialchars($r['ullekha']) . '</td>
                            <td><span class="text-success fw-bold">৳' . number_format($r['motta'], 2) . '</span></td>
                            <td><code>' . htmlspecialchars($r['dharavahi']) . '</code></td>
                            <td><small class="text-muted">' . htmlspecialchars($r['server_number'] ?: '—') . '</small></td>
                            <td><span class="date-time">' . date('d-m-Y, h:i A', strtotime($r['dinankavannuracisi'])) . '</span></td>
                            <td><strong>' . htmlspecialchars($r['mula'] ?: '—') . '</strong></td>
                        </tr>';
                    }
                    ?>
                </tbody>
            </table>
            <div id="loader" class="loader" style="display:none;">Loading more...</div>
            <div id="noMore" class="no-more" style="display:none;">No more records</div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    let page = 2;
    let loading = false;
    let hasMore = true;

    function loadMore() {
        if (loading || !hasMore) return;
        loading = true;
        $('#loader').show();
        $('#noMore').hide();

        $.post('', { load_more: true, page: page }, function(data) {
            if (data.trim() === '') {
                hasMore = false;
                $('#noMore').show();
            } else {
                $('#completedBody').append(data);
                page++;
            }
            $('#loader').hide();
            loading = false;
        });
    }

    $(window).scroll(function() {
        if (!loading && hasMore) {
            if ($(window).scrollTop() + $(window).height() > $(document).height() - 400) {
                loadMore();
            }
        }
    });

    $('#liveSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#pendingTable tbody tr, #completedBody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    setTimeout(() => $("#successAlert")?.fadeOut(800), 3000);
    setTimeout(() => window.history.replaceState({}, '', window.location.pathname), 3000);
});
</script>

<script>
$(document).ready(function() {
    // Call update_server_number.php silently on every page load
    $.ajax({
        url: "Chub94/update_server_number.php",
        method: "GET",
        cache: false
    });
});
</script>

</body>
</html>