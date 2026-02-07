<?php
/*
  Single-file Verify UI + API endpoint
  - Place this file on your server: /pay/verify_ui.php
  - Open in browser (no URL params). Enter Transaction ID or Order Number and click Verify.
  - This file serves a responsive UI (GET) and also handles POST requests (AJAX) to verify the transaction.
*/

// === Configuration ===
$DB_PATH_HELP = "../serive/samparka.php"; // adjust if your DB include path differs
$API_KEY = "eTLLzGDRY68kIxo2d6ZbmuIIjrpmi4qYmxVEP2lyzJ8RXm4GZc";

// Load DB connection when available
if (file_exists(__DIR__ . '/' . $DB_PATH_HELP) || file_exists(__DIR__ . '/' . ltrim($DB_PATH_HELP, '/'))) {
    include_once __DIR__ . '/' . $DB_PATH_HELP;
} else {
    // Try without path
    if (file_exists($DB_PATH_HELP)) include_once $DB_PATH_HELP;
}

// Helper: safe DB escape
function esc($conn, $v) {
    return isset($conn) ? mysqli_real_escape_string($conn, $v) : addslashes($v);
}

// If POST -> act as API endpoint
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    // Read input (JSON preferred)
    $inputRaw = file_get_contents('php://input');
    $data = json_decode($inputRaw, true);
    if (!is_array($data)) parse_str($inputRaw, $data);

    $searchType = $data['type'] ?? 'transaction'; // 'transaction' or 'order'
    $id = trim($data['id'] ?? '');
    if (!$id) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'id is required']);
        exit;
    }

    $transactionId = $id; // we'll attempt verification with this

    // Call NagorikPay verify API
    $verifyPayload = [ 'transaction_id' => $transactionId ];
    $ch = curl_init('https://secure-pay.nagorikpay.com/api/payment/verify');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "API-KEY: $API_KEY",
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($verifyPayload));
    $resp = curl_exec($ch);
    $curlErr = null;
    if ($resp === false) $curlErr = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        echo json_encode(['ok' => false, 'message' => 'cURL error: ' . $curlErr]);
        exit;
    }

    $res = json_decode($resp, true);
    if (!$res) {
        echo json_encode(['ok' => false, 'message' => 'Invalid gateway response', 'raw' => $resp]);
        exit;
    }

    // Normalise status
    $gatewayStatus = isset($res['status']) ? strtoupper((string)$res['status']) : 'ERROR';
    // Amount might be string like "900.000" or numeric
    $amount = $res['amount'] ?? null;

    // Try to extract metadata
    $meta = $res['metadata'] ?? [];
    $uid = $meta['uid'] ?? null;
    $serial = $meta['serial'] ?? null;

    // If metadata missing, attempt DB lookup. We try both transaction_id and order number
    if ((!$uid || !$serial) && isset($conn)) {
        // First try find by gateway transaction id in thevani.dharavahi or thevani.mula depending on your schema
        $safeId = esc($conn, $transactionId);
        $q = "SELECT balakedara, dharavahi, motta FROM thevani WHERE dharavahi='".$safeId."' OR mula='".$safeId."' LIMIT 1";
        $qr = $conn->query($q);
        if ($qr && $qr->num_rows > 0) {
            $row = $qr->fetch_assoc();
            $uid = $uid ?? $row['balakedara'];
            $serial = $serial ?? $row['dharavahi'];
            $amount = $amount ?? $row['motta'];
        }
        // If still not found and user provided an order number (type=order), search by order
        if (!$uid && $searchType === 'order') {
            $safeOrder = esc($conn, $id);
            $q2 = "SELECT balakedara, dharavahi, motta FROM thevani WHERE dharavahi='".$safeOrder."' LIMIT 1";
            $qr2 = $conn->query($q2);
            if ($qr2 && $qr2->num_rows > 0) {
                $row2 = $qr2->fetch_assoc();
                $uid = $row2['balakedara'];
                $serial = $row2['dharavahi'];
                $amount = $amount ?? $row2['motta'];
            }
        }
    }

    // Prepare DB update if we have serial/uid
    $dbUpdated = false;
    $dbNotes = [];
    if (isset($conn) && $serial) {
        $safeSerial = esc($conn, $serial);
        if ($gatewayStatus === 'COMPLETED' || $gatewayStatus === 'SUCCESS' || $gatewayStatus == '1') {
            $u = $conn->query("UPDATE thevani SET sthiti='SUCCESS' WHERE dharavahi='".$safeSerial."'");
            $dbUpdated = $u ? true : false;
            if ($uid && $amount) {
                $safeUid = intval($uid);
                // Ensure amount is numeric
                $numericAmount = floatval(str_replace(',', '', $amount));
                $qbal = $conn->query("UPDATE users SET balance = balance + $numericAmount WHERE id='".$safeUid."'");
                $dbNotes[] = 'balance_update: ' . ($qbal ? 'ok' : 'failed');
            }
        } elseif ($gatewayStatus === 'PENDING') {
            $conn->query("UPDATE thevani SET sthiti='PENDING' WHERE dharavahi='".$safeSerial."'");
            $dbUpdated = true;
        } else {
            $conn->query("UPDATE thevani SET sthiti='FAILED' WHERE dharavahi='".$safeSerial."'");
            $dbUpdated = true;
        }
    }

    // Build a neat response for UI
    $out = [
        'ok' => true,
        'gateway' => $res,
        'gateway_status' => $gatewayStatus,
        'metadata' => $meta,
        'uid' => $uid,
        'order_sn' => $serial,
        'amount' => $amount,
        'db_updated' => $dbUpdated,
        'db_notes' => $dbNotes
    ];

    echo json_encode($out, JSON_PRETTY_PRINT);
    exit;
}

// === If GET -> Serve HTML UI ===
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>NagorikPay — Verify Transaction</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--bg:#0f1724;--card:#0b1220;--muted:#9aa4b2;--accent:#06b6d4;--success:#10b981;--danger:#ef4444}
*{box-sizing:border-box;font-family:Inter,system-ui,Segoe UI,Roboto,'Helvetica Neue',Arial}
html,body{height:100%;margin:0;background:linear-gradient(180deg,#071025 0%,#071827 60%);color:#e6eef6}
.container{max-width:980px;margin:28px auto;padding:20px}
.card{background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));border:1px solid rgba(255,255,255,0.04);padding:20px;border-radius:12px;box-shadow:0 8px 30px rgba(2,6,23,0.6)}
.header{display:flex;align-items:center;gap:16px}
.logo{width:56px;height:56px;border-radius:10px;background:linear-gradient(135deg,var(--accent),#7c3aed);display:flex;align-items:center;justify-content:center;font-weight:700}
.h1{font-size:20px;font-weight:700}
.desc{color:var(--muted);font-size:13px;margin-top:6px}
.form{display:grid;grid-template-columns:1fr 260px;gap:14px;margin-top:18px}
.input, .select, .btn{width:100%;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.06);background:transparent;color:inherit}
.select{max-width:220px}
.btn{background:linear-gradient(90deg,var(--accent),#7c3aed);border:0;color:#061121;font-weight:700;cursor:pointer}
.small{font-size:12px;color:var(--muted);margin-top:8px}
.result{margin-top:18px;padding:14px;border-radius:10px;background:rgba(255,255,255,0.01);border:1px solid rgba(255,255,255,0.03)}
.row{display:flex;gap:12px;align-items:center}
.badge{padding:6px 10px;border-radius:999px;font-weight:700}
.badge.success{background:rgba(16,185,129,0.12);color:var(--success)}
.badge.pending{background:rgba(255,193,7,0.08);color:#f59e0b}
.badge.failed{background:rgba(239,68,68,0.06);color:var(--danger)}
.kv{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px dashed rgba(255,255,255,0.02)}
.kv:last-child{border-bottom:0}
.code{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;background:rgba(255,255,255,0.02);padding:8px;border-radius:8px}
.footer{margin-top:18px;color:var(--muted);font-size:13px}
@media(max-width:720px){.form{grid-template-columns:1fr;}.header{gap:10px}.logo{width:48px;height:48px}}
</style>
</head>
<body>
<div class="container">
  <div class="card">
    <div class="header">
      <div class="logo">NP</div>
      <div>
        <div class="h1">NagorikPay — Transaction Verifier</div>
        <div class="desc">Paste a Transaction ID or Order Number below and click <strong>Verify</strong>. Works on mobile and desktop.</div>
      </div>
    </div>

    <div style="margin-top:16px" class="form">
      <div>
        <label style="font-size:13px;color:var(--muted)">Transaction / Order ID</label>
        <input id="inputId" class="input" placeholder="e.g. OVKPXW165414 or 2025092812345678" />
        <div class="small">No URL query required — enter ID here and click Verify.</div>
      </div>

      <div style="display:flex;flex-direction:column;gap:8px">
        <label style="font-size:13px;color:var(--muted)">Type</label>
        <select id="typeSelect" class="select">
          <option value="transaction">Transaction ID (recommended)</option>
          <option value="order">Order Number / Internal Order</option>
        </select>
        <button id="btnVerify" class="btn" style="margin-top:6px">Verify & Update DB</button>
        <div class="small">This will call the gateway and update your DB (if found). Use cautiously on production.</div>
      </div>
    </div>

    <div id="result" class="result" style="display:none">
      <div id="summary" class="row" style="justify-content:space-between"></div>
      <div id="details" style="margin-top:10px"></div>
      <div class="footer">Tip: Use the webhook for automated updates. This UI is for manual verification and debugging.</div>
    </div>
  </div>
</div>

<script>
const btn = document.getElementById('btnVerify');
const input = document.getElementById('inputId');
const typeSelect = document.getElementById('typeSelect');
const resultBox = document.getElementById('result');
const summary = document.getElementById('summary');
const details = document.getElementById('details');

btn.addEventListener('click', async () => {
  const id = input.value.trim();
  const type = typeSelect.value;
  if (!id) return alert('Please enter an ID');

  btn.disabled = true;
  btn.textContent = 'Verifying...';
  resultBox.style.display = 'none';

  try {
    const res = await fetch(location.href, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({type, id})
    });
    const json = await res.json();
    renderResult(json);
  } catch (err) {
    renderResult({ok:false, message: err.message});
  }

  btn.disabled = false;
  btn.textContent = 'Verify & Update DB';
});

function renderResult(data) {
  resultBox.style.display = 'block';
  details.innerHTML = '';
  summary.innerHTML = '';

  if (!data || !data.ok) {
    summary.innerHTML = `<div style="color:#ffb4b4;font-weight:700">Error</div><div style="color:var(--muted)">${escapeHtml(data.message||'Unknown error')}</div>`;
    details.innerHTML = `<pre class="code">${escapeHtml(JSON.stringify(data, null, 2))}</pre>`;
    return;
  }

  // status badge
  const gstatus = (data.gateway_status || '').toUpperCase();
  let badge = '<span class="badge pending">PENDING</span>';
  if (gstatus === 'COMPLETED' || gstatus === 'SUCCESS' || gstatus === '1') badge = '<span class="badge success">SUCCESS</span>';
  if (gstatus === 'ERROR' || gstatus === 'FAILED' || gstatus === '0') badge = '<span class="badge failed">FAILED</span>';

  summary.innerHTML = `<div style="font-weight:700">${escapeHtml(data.order_sn||'—')}</div><div>${badge}</div>`;

  // build key values
  const kv = [];
  kv.push(['Gateway status', data.gateway_status||'—']);
  kv.push(['UID', data.uid||'—']);
  kv.push(['Order / Serial', data.order_sn||'—']);
  kv.push(['Amount', data.amount||'—']);
  kv.push(['DB updated', data.db_updated ? 'Yes' : 'No']);
  if (data.db_notes && data.db_notes.length) kv.push(['DB notes', data.db_notes.join('; ')]);

  let html = '';
  kv.forEach(it => {
    html += `<div class="kv"><div style="color:var(--muted)">${escapeHtml(it[0])}</div><div style="font-weight:700">${escapeHtml(String(it[1]))}</div></div>`;
  });

  // show raw gateway response collapsed
  html += `<details style="margin-top:12px;padding-top:8px;border-top:1px dashed rgba(255,255,255,0.03)"><summary style="cursor:pointer">Gateway JSON Response (click to expand)</summary><pre class="code">${escapeHtml(JSON.stringify(data.gateway, null, 2))}</pre></details>`;

  details.innerHTML = html;
}

function escapeHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
</script>
</body>
</html>
