<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}
include "conn.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid ID");
}
$withdraw_id = (int)$_GET['id'];

// Fetch data
$sql = "SELECT w.balakedara, w.motta, w.sthiti, w.dinankavannuracisi, w.khateshonu,
               k.phalanubhavi, k.kod, k.khatehesaru, k.khatesankhye,
               b.name AS bank_name, b.type AS payoutType, b.account AS accountNumber,
               s.mobile
        FROM hintegedukolli w
        LEFT JOIN khate k ON k.shonu = w.khateshonu
        LEFT JOIN bankcard b ON b.id = w.khateshonu
        LEFT JOIN shonu_subjects s ON s.id = w.balakedara
        WHERE w.shonu = ? LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $withdraw_id);
$stmt->execute();
$Result = $stmt->get_result()->fetch_assoc();

if (!$Result) {
    die("Withdrawal not found.");
}

// Fallback User ID
$user_id = $Result['balakedara'];
$mobile = $Result['mobile'];
if (!$user_id && $mobile) {
    $stmt2 = $conn->prepare("SELECT id FROM shonu_subjects WHERE mobile = ? LIMIT 1");
    $stmt2->bind_param("s", $mobile);
    $stmt2->execute();
    $res = $stmt2->get_result();
    if ($row = $res->fetch_assoc()) $user_id = $row['id'];
    $stmt2->close();
}
$stmt->close();

// Payout Logic
$isTRC = $Result['khatehesaru'] === 'TRC';
$bankName = $isTRC ? 'TRC20 (Crypto)' : ($Result['payoutType']=='175'?'bKash':($Result['payoutType']=='178'?'Nagad':($Result['payoutType']=='181'?'Upay':($Result['payoutType']=='182'?'Rocket Pay':'Bank'))));

$account = $isTRC ? $Result['khatesankhye'] : $Result['accountNumber'];
$beneficiary = $isTRC ? $Result['phalanubhavi'] : $Result['bank_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Withdraw #<?= $withdraw_id ?></title>

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root {
      --toast-bg: rgba(0, 0, 0, 0.85);
      --toast-text: #ffffff;
      --primary: #4361ee;
      --success: #10b981;
      --danger: #ef4444;
    }

    /* Responsive Content */
    .content-wrapper {
      padding: 1.5rem;
      min-height: calc(100vh - 60px);
    }
    @media (min-width: 768px) {
      .content-wrapper { padding: 2rem; }
    }

    /* Info Cards */
    .info-card {
      background: #fff;
      border-radius: 16px;
      padding: 1.8rem;
      box-shadow: 0 6px 20px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
      margin-bottom: 1.5rem;
    }
    .info-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 30px rgba(0,0,0,0.12);
    }
    .info-label {
      font-weight: 600;
      color: #4b5563;
      font-size: 1rem;
    }
    .info-value {
      font-size: 1.1rem;
      color: #1f2937;
      font-weight: 500;
    }
    .copy-btn {
      cursor: pointer;
      color: var(--primary);
      font-size: 1.3rem;
      margin-left: 10px;
      transition: 0.2s;
    }
    .copy-btn:hover { color: #1d4ed8; }

    /* Status Badge */
    .status-badge {
      padding: 0.4rem 1rem;
      border-radius: 50px;
      font-weight: 600;
      font-size: 0.9rem;
    }

    /* Action Buttons */
    .btn-action {
      min-width: 140px;
      font-weight: 600;
      border-radius: 12px;
      padding: 0.75rem 1.5rem;
      transition: all 0.3s ease;
    }
    .btn-action:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    /* Toast - Smooth, Transparent, Responsive */
    #copyToast {
      position: fixed;
      bottom: 20px;
      right: 20px;
      z-index: 9999;
      min-width: 260px;
      background: var(--toast-bg);
      color: var(--toast-text);
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.2);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      animation: slideIn 0.4s ease, fadeOut 0.4s ease 2.6s forwards;
    }
    @keyframes slideIn {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    @keyframes fadeOut {
      to { opacity: 0; transform: translateX(100%); }
    }

    /* Modal - Smooth & Modern */
    .modal-content {
      border-radius: 16px;
      border: none;
      box-shadow: 0 15px 40px rgba(0,0,0,0.2);
      overflow: hidden;
    }
    .modal-header {
      border-bottom: none;
      padding: 1.5rem 1.5rem 0;
    }
    .modal-body {
      padding: 1rem 1.5rem;
    }
    .modal-footer {
      border-top: none;
      padding: 0 1.5rem 1.5rem;
    }
    .modal-title {
      font-weight: 700;
      color: #1f2937;
    }
    #actionText {
      color: var(--primary);
      font-weight: 700;
    }
  </style>
</head>
<body>

  <!-- ONLY HEADER.PHP -->
  <?php include 'header.php'; ?>

  <!-- MAIN CONTENT -->
  <div class="content-wrapper">

    <!-- Title -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
      <h4 class="font-weight-bold text-dark mb-0">
        Withdraw Request #<?= $withdraw_id ?>
      </h4>
      <span class="status-badge 
        <?= $Result['sthiti'] == 0 ? 'bg-warning text-dark' : ($Result['sthiti'] == 1 ? 'bg-success text-white' : 'bg-danger text-white') ?>">
        <?= $Result['sthiti'] == 0 ? 'Pending' : ($Result['sthiti'] == 1 ? 'Accepted' : 'Rejected') ?>
      </span>
    </div>

    <div class="row g-4">
      <!-- User Info -->
      <div class="col-lg-6">
        <div class="info-card">
          <h5 class="mb-3 text-primary"><i class="bi bi-person-circle"></i> User Details</h5>
          <div class="row mb-3">
            <div class="col-5 info-label">Mobile</div>
            <div class="col-7 info-value"><?= htmlspecialchars($mobile ?? '—') ?></div>
          </div>
          <hr class="my-3">
          <div class="row mb-3">
            <div class="col-5 info-label">User ID</div>
            <div class="col-7 info-value"><strong>#<?= $user_id ?: '—' ?></strong></div>
          </div>
          <hr class="my-3">
          <div class="row">
            <div class="col-5 info-label">Date</div>
            <div class="col-7 info-value"><?= date('d M Y, h:i A', strtotime($Result['dinankavannuracisi'])) ?></div>
          </div>
        </div>
      </div>

      <!-- Payout Info -->
      <div class="col-lg-6">
        <div class="info-card">
          <h5 class="mb-3 text-success"><i class="bi bi-wallet2"></i> Payout Details</h5>
          <div class="row mb-3">
            <div class="col-5 info-label">Amount</div>
            <div class="col-7 info-value text-danger fs-5">৳ <?= number_format($Result['motta'], 2) ?></div>
          </div>
          <hr class="my-3">
          <div class="row mb-3">
            <div class="col-5 info-label">Method</div>
            <div class="col-7 info-value"><?= $bankName ?></div>
          </div>
          <hr class="my-3">
          <div class="row mb-3 align-items-center">
            <div class="col-5 info-label">Beneficiary</div>
            <div class="col-7 info-value">
              <?= htmlspecialchars($beneficiary ?? '—') ?>
              <i class="bi bi-copy copy-btn" onclick="copyToClip('<?= htmlspecialchars($beneficiary) ?>')"></i>
            </div>
          </div>
          <hr class="my-3">
          <div class="row mb-3 align-items-center">
            <div class="col-5 info-label">Account</div>
            <div class="col-7 info-value">
              <code><?= htmlspecialchars($account) ?></code>
              <i class="bi bi-copy copy-btn" onclick="copyToClip('<?= htmlspecialchars($account) ?>')"></i>
            </div>
          </div>
          <?php if ($isTRC && $Result['kod']): ?>
          <hr class="my-3">
          <div class="row">
            <div class="col-5 info-label">Memo/Tag</div>
            <div class="col-7 info-value"><code><?= htmlspecialchars($Result['kod']) ?></code></div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <?php if ($Result['sthiti'] == 0): ?>
    <div class="text-center mt-4">
      <button class="btn btn-success btn-action me-3" onclick="openConfirm('accept')">
        <i class="bi bi-check-circle"></i> Accept
      </button>
      <button class="btn btn-danger btn-action" onclick="openConfirm('reject')">
        <i class="bi bi-x-circle"></i> Reject
      </button>
    </div>
    <?php endif; ?>

  </div>

  <!-- Toast: Copied -->
  <div id="copyToast" class="toast align-items-center text-white border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body fs-6">
        <i class="bi bi-check-circle me-2"></i> Copied to clipboard!
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>

  <!-- Modal: Accept/Reject -->
  <div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Confirm Action</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
          <p class="mb-3">Are you sure you want to <strong id="actionText"></strong> this withdrawal?</p>
          <div class="mb-3">
            <input type="text" id="remark" class="form-control form-control-sm" placeholder="Remark (optional)">
          </div>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary btn-sm" id="confirmBtn">Confirm</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    let actionType = '';
    const withdrawalId = <?= $withdraw_id ?>;

    // Copy to Clipboard + Toast
    function copyToClip(text) {
      navigator.clipboard.writeText(text).then(() => {
        const toast = new bootstrap.Toast(document.getElementById('copyToast'));
        toast.show();
      });
    }

    // Open Modal
    function openConfirm(type) {
      actionType = type;
      const isAccept = type === 'accept';
      document.getElementById('actionText').textContent = isAccept ? 'ACCEPT' : 'REJECT';
      document.getElementById('actionText').style.color = isAccept ? 'var(--success)' : 'var(--danger)';
      document.getElementById('modalTitle').textContent = isAccept ? 'Accept Withdrawal' : 'Reject Withdrawal';
      document.getElementById('confirmBtn').className = isAccept ? 'btn btn-success btn-sm' : 'btn btn-danger btn-sm';
      new bootstrap.Modal(document.getElementById('confirmModal')).show();
    }

    // Confirm Action
    document.getElementById('confirmBtn').addEventListener('click', function () {
      const remark = document.getElementById('remark').value.trim();
      const data = new FormData();
      data.append('id', withdrawalId);
      data.append('type', actionType);
      data.append('remark', remark);

      fetch('manage_withdrawAction.php', { method: 'POST', body: data })
        .then(r => r.text())
        .then(res => {
          window.location = res.trim() === '1' ? 'withdraw_accept_list.php' : 'withdraw_reject_list.php';
        });
    });
  </script>
</body>
</html>