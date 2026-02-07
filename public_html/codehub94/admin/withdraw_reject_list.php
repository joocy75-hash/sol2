<?php
session_start();
if($_SESSION['unohs'] == null){
    header("location:index.php?msg=unauthorized");
    exit;
}
include "conn.php";
include "header.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdraw Reject List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<style>
     
        .badge-method {
            padding: 0.4rem 0.9rem; border-radius: 50px; font-size: 0.8rem; font-weight: 600;
        }
        .detail-btn {
            background: var(--success); color: white; border: none;
            padding: 0.5rem 1rem; border-radius: 12px; font-size: 0.85rem;
        }
        .detail-btn:hover { background: #059669; transform: translateY(-2px); }
        .modal-content { border-radius: 20px; overflow: hidden; }
        @media (max-width: 768px) {
            .stats-row { flex-direction: column; }
            .stat-box { padding: 1.5rem; }
            .stat-value { font-size: 2.3rem; }
            table { font-size: 0.9rem; }
            .detail-btn { padding: 0.4rem 0.8rem; font-size: 0.8rem; }
        }
        .badge {
    color: #000 !important;
    font-weight: 600 !important;
    background-color: #e2e8f0 !important; /* Light gray */
}

    </style>
    
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

        /* LEFT & RIGHT BOXES */
        .stats-row {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 2rem;
            flex-wrap: nowrap;
        }
        .stat-box {
            flex: 1;
            background: white;
            padding: 1.8rem;
            border-radius: 24px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.1);
            text-align: center;
            border: 1px solid #e2e8f0;
            min-width: 0;
        }
        .stat-label {
            font-size: 1.05rem;
            color: #64748b;
            font-weight: 600;
        }
        .stat-value {
            font-size: 2.8rem;
            font-weight: 800;
            color: var(--blue);
            margin-top: 0.8rem;
        }

        /* CLEAN SEARCH BOX - ONLY ONE, NO BUTTON */
        .search-container {
            position: relative;
            max-width: 500px;
            margin: 0 0 2.5rem auto;
        }
        .search-box {
            width: 100%;
            padding: 1rem 1.8rem 1rem 4.5rem;
            font-size: 1.1rem;
            border: 2px solid #cbd5e1;
            border-radius: 50px;
            background: white;
            transition: border-color 0.3s ease;
            outline: none;
            font-weight: 500;
        }
        .search-box::placeholder {
            color: #94a3b8;
            font-weight: 500;
        }
        .search-box:focus {
            border-color: var(--blue-light);
        }
        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--blue);
            font-size: 1.4rem;
            pointer-events: none;
            z-index: 10;
        }

        /* Table Card */
        .table-card {
            background: white;
            border-radius: 26px;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0,0,0,0.14);
            border: 1px solid #e2e8f0;
        }

        .table thead {
            background: linear-gradient(135deg, var(--blue), var(--blue-light));
            color: white;
            font-weight: 600;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .table tbody tr:hover {
            background: #f8faff !important;
        }

        /* PLUS ICON */
        .dt-button.buttons-responsive {
            background: linear-gradient(135deg, var(--blue), var(--blue-light)) !important;
            color: white !important;
            border-radius: 50% !important;
            width: 56px !important;
            height: 56px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 1.6rem !important;
            font-weight: bold !important;
            box-shadow: 0 12px 35px rgba(30,64,175,0.5) !important;
            transition: all 0.4s ease !important;
            margin: 0 auto !important;
        }
        .dt-button.buttons-responsive:hover {
            transform: scale(1.15);
        }

        /* MOBILE */
        @media (max-width: 768px) {
            .main-container { padding: 1rem; }
            .page-title { font-size: 1.4rem; }
            .stats-row { gap: 1rem; }
            .stat-box { padding: 1.4rem; }
            .stat-value { font-size: 2.4rem; }
            .search-container { max-width: 100%; }
            .search-box { 
                padding: 0.9rem 1.5rem 0.9rem 4.2rem; 
                font-size: 1rem; 
            }
            .search-icon { left: 18px; font-size: 1.3rem; }
        }
        @media (max-width: 480px) {
            .main-container { padding: 0.8rem; }
            .page-title { font-size: 1.3rem; }
            .stats-row { flex-direction: row; gap: 0.9rem; }
            .stat-box { padding: 1.2rem; }
            .stat-value { font-size: 2.1rem; }
        }
    </style>
</head>
<body>
<div class="main-container">
    <h2 class="page-title mb-4">Withdraw Reject List</h2>

    <?php
    $Query = mysqli_query($conn, "SELECT COUNT(*) as total, SUM(motta) as amount FROM hintegedukolli WHERE sthiti='2'");
    $stats = mysqli_fetch_array($Query);
    ?>
    <div class="stats-row d-flex">
        <div class="stat-box">
            <div class="stat-label text-muted">Total Rejected</div>
            <div class="stat-value"><?= $stats['total'] ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-label text-muted">Total Rejected Amount</div>
            <div class="stat-value">৳<?= number_format($stats['amount'], 0) ?></div>
        </div>
    </div>

    <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="liveSearch" class="form-control search-box" placeholder="Search by Mobile / Order ID / Account...">
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table id="rejectTable" class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Mobile</th>
                        <th>Method</th>
                        <th>User ID</th>
                        <th>Amount</th>
                        <th>Order ID</th>
                        <th>Account</th>
                        <th>Date & Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
$q = mysqli_query($conn, "
    SELECT w.*, s.mobile, s.id as userid,
           COALESCE(b.type, k.khatehesaru) as method_type,
           COALESCE(b.account, k.khatesankhye) as account_no,
           COALESCE(b.name, k.phalanubhavi) as beneficiary,
           k.kod as memo_tag,
           w.remarks as admin_remarks
    FROM hintegedukolli w
    LEFT JOIN shonu_subjects s ON s.id = w.balakedara
    LEFT JOIN bankcard b ON b.id = w.khateshonu
    LEFT JOIN khate k ON k.shonu = w.khateshonu
    WHERE w.sthiti = '2'
    ORDER BY w.shonu DESC
");
while($r = mysqli_fetch_array($q)){
    $method = 'Bank'; 
    $badge = 'bg-secondary';

    if ($r['method_type'] == '175') { 
        $method = 'bKash'; 
        $badge = 'bg-danger'; 
    }
    elseif ($r['method_type'] == '178') { 
        $method = 'Nagad'; 
        $badge = 'bg-warning text-dark'; 
    }
    elseif ($r['method_type'] == '181') { 
        $method = 'Upay'; 
        $badge = 'bg-primary'; 
    }
    elseif ($r['method_type'] == '182') { 
        $method = 'Rocket Pay'; 
        $badge = 'bg-purple'; 
    }
    elseif ($r['khatehesaru'] == 'TRC' || $r['method_type'] == 'TRC') { 
        $method = 'TRC20'; 
        $badge = 'bg-info'; 
    }
?>

                    <tr>
                        <td><i class="fas fa-mobile-alt text-danger me-2"></i> <strong class="text-danger"><?= htmlspecialchars($r['mobile']) ?></strong></td>
                        <td><span class="badge <?= $badge ?> badge-method"><?= $method ?></span></td>
                        <td><span class="badge bg-dark">#<?= $r['userid'] ?></span></td>
                        <td><strong class="text-danger">৳<?= number_format($r['motta'],2) ?></strong></td>
                        <td><code class="bg-light px-3 py-2 rounded"><?= $r['dharavahi'] ?></code></td>
                        <td>
                            <code><?= htmlspecialchars($r['account_no']) ?></code>
                            <?php if($r['memo_tag']) echo "<br><small class='text-danger fw-bold'>Tag: ".$r['memo_tag']."</small>"; ?>
                        </td>
                        <td><small class="text-muted d-block"><?= date('d M Y', strtotime($r['dinankavannuracisi'])) ?><br><?= date('h:i A', strtotime($r['dinankavannuracisi'])) ?></small></td>
                        <td>
                            <button class="btn detail-btn shadow-lg" onclick="showRejectDetails(<?= htmlspecialchars(json_encode([
                                'mobile' => $r['mobile'],
                                'amount' => $r['motta'],
                                'method' => $method,
                                'account' => $r['account_no'],
                                'beneficiary' => $r['beneficiary'] ?? '—',
                                'tag' => $r['memo_tag'],
                                'remark' => $r['admin_remarks'] ?? 'No remark',
                                'date' => date('d M Y, h:i A', strtotime($r['dinankavannuracisi']))
                            ])) ?>)">
                                View
                            </button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL - SAME AS ACCEPT LIST -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"> Rejected Withdrawal Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="rejectModalBody"></div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$('#liveSearch').on('keyup', function() {
    var value = $(this).val().toLowerCase();
    $('#rejectTable tbody tr').filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
});

function showRejectDetails(data) {
    const body = `
        <div class="row g-3">
            <div class="col-6"><strong>Mobile:</strong></div>
            <div class="col-6 text-danger fw-bold">${data.mobile}</div>
            
            <div class="col-6"><strong>Amount:</strong></div>
            <div class="col-6 text-danger fw-bold">৳${parseFloat(data.amount).toFixed(2)}</div>
            
            <div class="col-6"><strong>Method:</strong></div>
            <div class="col-6">
                <span class="badge ${
                    data.method === 'bKash' ? 'bg-danger' : 
                    data.method === 'Nagad' ? 'bg-warning text-dark' : 
                    data.method === 'TRC20' ? 'bg-info' : 'bg-secondary'
                }">${data.method}</span>
            </div>
            
            <div class="col-6"><strong>Beneficiary:</strong></div>
            <div class="col-6 fw-bold">${data.beneficiary || '—'}</div>
            
            <div class="col-6"><strong>Account:</strong></div>
            <div class="col-6"><code>${data.account}</code></div>
            
            ${data.tag ? `
            <div class="col-6"><strong>Tag:</strong></div>
            <div class="col-6"><code class="text-danger fw-bold">${data.tag}</code></div>
            ` : ''}
            
            <div class="col-6"><strong>Date & Time:</strong></div>
            <div class="col-6">${data.date}</div>
            
            <div class="col-12 mt-4">
                <div class="alert alert-danger border-0">
                    <strong>Reject Reason:</strong><br>
                    ${data.remark === 'No remark' ? '<em class="text-muted">No reason provided</em>' : data.remark}
                </div>
            </div>
        </div>
    `;

    $('#rejectModalBody').html(body);
    new bootstrap.Modal('#rejectModal').show();
}

</script>
</body>
</html>