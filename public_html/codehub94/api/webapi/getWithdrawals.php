<?php
include "../../conn.php";
include "../../functions2.php";

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Credentials: true');
header('Vary: Origin');

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allow_origin = '';
if ($origin) {
    $stmt = $conn->prepare("SELECT domain FROM allowed_origins WHERE domain = ? AND status = 1");
    if ($stmt) {
        $stmt->bind_param("s", $origin);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $allow_origin = $origin;
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if ($allow_origin) header("Access-Control-Allow-Origin: $allow_origin");
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, ar-origin, ar-real-ip, ar-session');
    exit(0);
}
if ($allow_origin) header("Access-Control-Allow-Origin: $allow_origin");

date_default_timezone_set("Asia/Dhaka");
$shnunc = date("Y-m-d H:i:s");

$res = ['code' => 11, 'msg' => 'Method not allowed', 'msgCode' => 12, 'serviceNowTime' => $shnunc];
$shonubody = file_get_contents("php://input");
$shonupost = json_decode($shonubody, true);

if ($shonupost === null && json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode($res); exit;
}

function replaceWithAsterisks($s) {
    return strlen($s) < 10 ? $s : substr($s, 0, 6) . '****' . substr($s, -4);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode($res); exit;
}

$required = ['language', 'random', 'signature', 'timestamp', 'withdrawid'];
foreach ($required as $k) {
    if (!isset($shonupost[$k])) {
        $res['code'] = 7; $res['msg'] = 'Param is Invalid'; $res['msgCode'] = 6;
        echo json_encode($res); exit;
    }
}

$language = $conn->real_escape_string($shonupost['language']);
$random = $conn->real_escape_string($shonupost['random']);
$signature = $conn->real_escape_string($shonupost['signature']);
$withdrawid = (int)$shonupost['withdrawid'];

$shonustr = '{"language":' . $language . ',"random":"' . $random . '","withdrawid":' . $withdrawid . '}';
$shonusign = strtoupper(md5($shonustr));

if ($shonusign !== $signature) {
    $res['code'] = 5; $res['msg'] = 'Wrong signature'; $res['msgCode'] = 3;
    echo json_encode($res); exit;
}

$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (!preg_match('/Bearer\s+([^\s]+)/', $authHeader, $m)) {
    $res['code'] = 4; $res['msgCode'] = 2; http_response_code(401);
    echo json_encode($res); exit;
}

$author = $m[1];
$is_jwt_valid = is_jwt_valid($author);
$data_auth = json_decode($is_jwt_valid, true);

if (!$data_auth || ($data_auth['status'] ?? '') !== 'Success') {
    $res['code'] = 4; $res['msgCode'] = 2; http_response_code(401);
    echo json_encode($res); exit;
}

$shonuid = $data_auth['payload']['id'] ?? 0;
if ($shonuid <= 0) {
    $res['code'] = 4; echo json_encode($res); exit;
}

$stmt = $conn->prepare("SELECT akshinak FROM shonu_subjects WHERE akshinak = ? LIMIT 1");
$stmt->bind_param("s", $author);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows !== 1) {
    $res['code'] = 4; $res['msgCode'] = 2; http_response_code(401);
    echo json_encode($res); exit;
}
$stmt->close();

// === withdrawal_rules TABLE (CREATE ONLY IF NOT EXISTS) ===
$conn->query("
CREATE TABLE IF NOT EXISTS `withdrawal_rules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `withdraw_type` TINYINT NOT NULL DEFAULT 0 COMMENT '0=global, 3=TRC, 4=Wallet',
    `startTime` TIME NOT NULL DEFAULT '00:00:00',
    `endTime` TIME NOT NULL DEFAULT '23:59:59',
    `fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `minPrice` DECIMAL(12,2) NOT NULL DEFAULT 20.00,
    `maxPrice` DECIMAL(12,2) NOT NULL DEFAULT 50000.00,
    `bet_multiplier` DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    `need_to_bet_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `daily_withdraw_limit` TINYINT(2) NOT NULL DEFAULT 3,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_withdraw_type` (`withdraw_type`),
    INDEX `idx_type` (`withdraw_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// === INSERT DEFAULT RULES ONLY IF NOT EXIST (INSERT IGNORE) ===
$default_rules = [
    [0, '00:00:00', '23:59:59', 0.00, 20.00, 50000.00, 1.00, 1, 3],
    [3, '00:00:00', '23:59:59', 0.00, 20.00, 10000.00, 1.00, 1, 5],
    [4, '00:00:00', '23:59:59', 0.00, 20.00, 50000.00, 1.00, 1, 3]
];

foreach ($default_rules as $r) {
    $stmt = $conn->prepare("
        INSERT IGNORE INTO withdrawal_rules
        (withdraw_type, startTime, endTime, fee, minPrice, maxPrice, bet_multiplier, need_to_bet_enabled, daily_withdraw_limit)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issddddii", ...$r);
    $stmt->execute();
    $stmt->close();
}

$data = [];

// === WITHDRAWAL LIST ===
if ($withdrawid == 4) {
    $stmt = $conn->prepare("SELECT * FROM bankcard WHERE userid = ? AND type != '3' ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $shonuid);
    $stmt->execute(); $resu = $stmt->get_result();
    if ($resu->num_rows > 0) {
        $row = $resu->fetch_assoc(); $data['lastBandCarkName'] = $row['name'];
        $stmt2 = $conn->prepare("SELECT * FROM bankcard WHERE userid = ? AND type != '3' ORDER BY id DESC");
        $stmt2->bind_param("i", $shonuid); $stmt2->execute(); $res2 = $stmt2->get_result();
        $i = 0;
        while ($r = $res2->fetch_assoc()) {
            $bt = match($r['type']) { 175=>'BKASH', 178=>'NAGAD',181=>'Upay',182=>'Rocket Pay', default=>'OTHER' };
            $data['withdrawalslist'][$i++] = [
                'bid' => $r['id'], 'bankName' => $bt, 'walletName' => $bt,
                'beneficiaryName' => "$bt | ".$r['name'],
                'accountNo' => replaceWithAsterisks($r['account']),
                'ifsCode' => 'ifsc', 'withType' => $r['type'],
                'mobileNO' => replaceWithAsterisks($r['account']),
                'bankProvince' => 'a', 'bankCity' => 'b', 'bankAddress' => 'c'
            ];
        }
        $stmt2->close();
    } else {
        $data['lastBandCarkName'] = null; $data['withdrawalslist'] = [];
    }
    $stmt->close();
}
elseif ($withdrawid == 3) {
    $stmt = $conn->prepare("SELECT phalanubhavi FROM khate WHERE byabaharkarta = ? AND khatehesaru = 'TRC' ORDER BY shonu DESC LIMIT 1");
    $stmt->bind_param("i", $shonuid); $stmt->execute(); $resu = $stmt->get_result();
    if ($resu->num_rows > 0) {
        $row = $resu->fetch_assoc(); $data['lastBandCarkName'] = $row['phalanubhavi'];
        $stmt2 = $conn->prepare("SELECT shonu, khatehesaru, khatesankhye, kod, duravani FROM khate WHERE byabaharkarta = ? AND khatehesaru = 'TRC' ORDER BY shonu DESC");
        $stmt2->bind_param("i", $shonuid); $stmt2->execute(); $res2 = $stmt2->get_result();
        $i = 0;
        while ($r = $res2->fetch_assoc()) {
            $data['withdrawalslist'][$i++] = [
                'bid' => $r['shonu'], 'bankName' => $r['khatehesaru'], 'beneficiaryName' => '',
                'accountNo' => replaceWithAsterisks($r['khatesankhye']), 'ifsCode' => $r['kod'],
                'withType' => 1, 'mobileNo' => replaceWithAsterisks($r['duravani']),
                'bankProvince' => '', 'bankCity' => '', 'bankAddress' => ''
            ];
        }
        $stmt2->close();
    } else {
        $data['lastBandCarkName'] = null; $data['withdrawalslist'] = [];
    }
    $stmt->close();
}

// === TODAY'S WITHDRAW COUNT ===
$stmt = $conn->prepare("SELECT COUNT(*) as c FROM hintegedukolli WHERE balakedara = ? AND DATE(dinankavannuracisi) = DATE(?)");
$stmt->bind_param("is", $shonuid, $shnunc); $stmt->execute();
$today_withdraw_count = $stmt->get_result()->fetch_assoc()['c'] ?? 0; $stmt->close();

// === LOAD RULE FROM DB ===
$rule_type = ($withdrawid == 3) ? 3 : (($withdrawid == 4) ? 4 : 0);
$stmt = $conn->prepare("
    SELECT startTime, endTime, fee, minPrice, maxPrice, bet_multiplier, need_to_bet_enabled, daily_withdraw_limit
    FROM withdrawal_rules
    WHERE withdraw_type IN (0,?)
    ORDER BY FIELD(withdraw_type,?,0)
    LIMIT 1
");
$stmt->bind_param("ii", $rule_type, $rule_type); $stmt->execute(); $rr = $stmt->get_result();
$rule = $rr->fetch_assoc();

if (!$rule) {
    $rule = [
        'startTime'=>'00:00:00','endTime'=>'23:59:59','fee'=>0,'minPrice'=>20,'maxPrice'=>50000,
        'bet_multiplier'=>1.00,'need_to_bet_enabled'=>1,'daily_withdraw_limit'=>3
    ];
}
$stmt->close();

$daily_limit = (int)$rule['daily_withdraw_limit'];
$remaining_count = max(0, $daily_limit - $today_withdraw_count);
$need_to_bet_enabled = (bool)$rule['need_to_bet_enabled'];
$bet_multiplier = (float)$rule['bet_multiplier'];

$data["withdrawalsrule"] = [
    "withdrawCount" => (int)$today_withdraw_count,
    "withdrawRemainingCount" => (int)$remaining_count,
    "startTime" => $rule['startTime'],
    "endTime" => $rule['endTime'],
    "fee" => (float)$rule['fee'],
    "minPrice" => (float)$rule['minPrice'],
    "maxPrice" => (float)$rule['maxPrice']
];

// === USER BALANCE ===
$stmt = $conn->prepare("SELECT motta FROM shonu_kaichila WHERE balakedara = ? LIMIT 1");
$stmt->bind_param("i", $shonuid); $stmt->execute();
$balance = $stmt->get_result()->fetch_assoc()['motta'] ?? 0; $stmt->close();
$data["withdrawalsrule"]["amount"] = (float)$balance;

// === RECHARGE TOTAL ===
$recharge = 0;
$stmt = $conn->prepare("SELECT COALESCE(SUM(motta),0) as s FROM thevani WHERE balakedara = ? AND sthiti = '1'");
$stmt->bind_param("i", $shonuid); $stmt->execute(); $recharge += $stmt->get_result()->fetch_assoc()['s']; $stmt->close();
$stmt = $conn->prepare("SELECT COALESCE(SUM(price),0) as s FROM hodike_balakedara WHERE userkani = ?");
$stmt->bind_param("i", $shonuid); $stmt->execute(); $recharge += $stmt->get_result()->fetch_assoc()['s']; $stmt->close();

// === TOTAL BETS ===
$bet_tables = ['bajikattuttate_trx','bajikattuttate_trx3','bajikattuttate_trx5','bajikattuttate_trx10','bajikattuttate','bajikattuttate_drei','bajikattuttate_funf','bajikattuttate_zehn','bajikattuttate_kemuru','bajikattuttate_kemuru_drei','bajikattuttate_kemuru_funf','bajikattuttate_kemuru_zehn','bajikattuttate_aidudi','bajikattuttate_aidudi_drei','bajikattuttate_aidudi_funf','bajikattuttate_aidudi_zehn'];
$real_total_bet = 0;
foreach ($bet_tables as $t) {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(ketebida),0) as s FROM `$t` WHERE byabaharkarta = ?");
    $stmt->bind_param("i", $shonuid); $stmt->execute();
    $real_total_bet += $stmt->get_result()->fetch_assoc()['s']; $stmt->close();
}

// === BET LOGIC ===
$required_bet = $recharge * $bet_multiplier;
$display_bet = $need_to_bet_enabled ? $real_total_bet : $required_bet;

$data["withdrawalsrule"]["requiredBet"] = round($required_bet, 2);
$data["withdrawalsrule"]["completedBet"] = round($display_bet, 2);
$data["withdrawalsrule"]["betProgress"] = $required_bet > 0 ? round(min(100, ($display_bet / $required_bet) * 100), 2) : 100;
$wiwo = ($display_bet >= $required_bet) ? $balance : 0;
$data["withdrawalsrule"]["amountofCode"] = ($display_bet >= $required_bet) ? 0 : round($required_bet - $display_bet, 2);
$data["withdrawalsrule"]["canWithdrawAmount"] = (float)$wiwo;
$data["withdrawalsrule"]["c2cUnitAmount"] = 0;
$data["withdrawalsrule"]["uRate"] = 93;
$data["withdrawalsrule"]["uGold"] = 0;

// === SUCCESS RESPONSE ===
$res['data'] = $data;
$res['code'] = 0; $res['msg'] = 'Succeed'; $res['msgCode'] = 0;
http_response_code(200);
echo json_encode($res, JSON_UNESCAPED_UNICODE);
exit;
?>