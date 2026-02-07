<?php
$conn = new mysqli("localhost", "u209477126_sol0203", "UP209477126_sol0203", "u209477126_sol0203");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$type = $_GET['type'] ?? 'wingo_1min';
$issue_search = $_GET['issue'] ?? '';
$start_time = $_GET['start_time'] ?? '';
$end_time = $_GET['end_time'] ?? '';

$table_map = [
    'wingo_30' => 'gellaluhogiondu_phalitansa_zehn',
    'wingo_1min' => 'gellaluhogiondu_phalitansa',
    'wingo_3min' => 'gellaluhogiondu_phalitansa_drei',
    'wingo_5min' => 'gellaluhogiondu_phalitansa_funf'
];

if (!array_key_exists($type, $table_map)) { die("Invalid type selected."); }
$table = $table_map[$type];

$where = "1=1";
if ($issue_search != '') { $where .= " AND kalaparichaya LIKE '%$issue_search%'"; }
if ($start_time != '') { $where .= " AND dinankavannuracisi >= '$start_time'"; }
if ($end_time != '') { $where .= " AND dinankavannuracisi <= '$end_time'"; }

$sql = "SELECT * FROM $table WHERE $where ORDER BY shonu DESC LIMIT 20";
$result = $conn->query($sql);
$rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$upcoming = $rows[0] ?? null;
?>

<?php include 'teacher_nav.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Wingo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body { font-family: Arial, sans-serif; }
        .header-info { font-weight: bold; font-size: 18px; margin-bottom: 10px; }
        .state-red { color: red; }
        .state-green { color: green; }
        table th, table td { text-align: center; }
        table thead { background-color: #fff; }
        .countdown { font-weight: bold; color: red; font-size: 18px; margin-left: 10px; }
    </style>
</head>
<body>
<div class="container mt-4">
    <form method="get" class="mb-2">
        <div class="form-row align-items-center">
            <div class="col-md-2">
                <input type="text" name="issue" value="<?= htmlspecialchars($issue_search) ?>" class="form-control" placeholder="Issue">
            </div>
            <div class="col-md-2">
                <select name="type" class="form-control" onchange="this.form.submit()">
                    <option value="wingo_30" <?= $type == 'wingo_30' ? 'selected' : '' ?>>Wingo 30 Sec</option>
                    <option value="wingo_1min" <?= $type == 'wingo_1min' ? 'selected' : '' ?>>Wingo 1 Min</option>
                    <option value="wingo_3min" <?= $type == 'wingo_3min' ? 'selected' : '' ?>>Wingo 3 Min</option>
                    <option value="wingo_5min" <?= $type == 'wingo_5min' ? 'selected' : '' ?>>Wingo 5 Min</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="datetime-local" name="start_time" value="<?= htmlspecialchars($start_time) ?>" class="form-control">
            </div>
            <div class="col-md-2">
                <input type="datetime-local" name="end_time" value="<?= htmlspecialchars($end_time) ?>" class="form-control">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-block">Search</button>
            </div>
            <div class="col-md-2">
                <a href="teacher_wingo.php" class="btn btn-secondary btn-block">Reset</a>
            </div>
        </div>
    </form>

    <?php if ($upcoming): ?>
        <div class="header-info">
            <?= htmlspecialchars($upcoming['kalaparichaya']) ?>
            <span class="countdown" id="countdown"></span>
        </div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Issue</th>
                <th>Lottery Results</th>
                <th>Mantissa</th>
                <th>Start Time</th>
                <th>State</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($rows)): ?>
            <!-- Upcoming row -->
            <tr>
                <td><?= htmlspecialchars($upcoming['kalaparichaya']) ?></td>
                <td>--</td>
                <td>--</td>
                <td><?= htmlspecialchars($upcoming['dinankavannuracisi']) ?></td>
                <td><span class="state-red">Unsettlement</span></td>
            </tr>
            <!-- Past rows -->
            <?php foreach ($rows as $row): ?>
                <?php if ($row['kalaparichaya'] == $upcoming['kalaparichaya']) continue; ?>
                <tr>
                    <td><?= htmlspecialchars($row['kalaparichaya']) ?></td>
                    <td><?= $row['bele'] != '' ? htmlspecialchars($row['bele']) : '--' ?></td>
                    <td><?= $row['phalitansa'] != '' ? htmlspecialchars($row['phalitansa']) : '--' ?></td>
                    <td><?= htmlspecialchars($row['dinankavannuracisi']) ?></td>
                    <td>
                        <?php if ($row['bele'] == '' || $row['bele'] == '--'): ?>
                            <span class="state-red">Unsettlement</span>
                        <?php else: ?>
                            <span class="state-green">Settled</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5">No records found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    let type = "<?= $type ?>";
    let countdownEl = document.getElementById('countdown');
    let durations = { wingo_30: 30, wingo_1min: 60, wingo_3min: 180, wingo_5min: 300 };
    let seconds = durations[type];

    function updateCountdown() {
        let min = Math.floor(seconds / 60);
        let sec = seconds % 60;
        countdownEl.textContent = `${min.toString().padStart(2, '0')}:${sec.toString().padStart(2, '0')}`;
        if (seconds > 0) seconds--;
        else seconds = durations[type];
    }
    if (countdownEl) setInterval(updateCountdown, 1000);
    updateCountdown();
</script>
</body>
</html>
