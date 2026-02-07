<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}
date_default_timezone_set("Asia/Dhaka");
include 'conn.php';

$fileUsageMap = [
    'style.css' => 'Login Page',
    'dashboard.css' => 'Dashboard',
    'theme.css' => 'Main Theme',
];

$cssDir = dirname(__DIR__) . '/assets/css/';
$cssFiles = glob($cssDir . '*.css');
$totalChangedFiles = 0;
$allColorsUsed = [];
$totalRGB = 0;

// Pagination
$perPage = 5;
$totalFiles = count($cssFiles);
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$start = ($page - 1) * $perPage;
$pagedFiles = array_slice($cssFiles, $start, $perPage);
$totalPages = ceil($totalFiles / $perPage);

// Handle RESET ALL
if (isset($_GET['reset_all']) && $_GET['reset_all'] == 1) {
    foreach ($cssFiles as $file) {
        if (file_exists($file . '.bak')) {
            copy($file . '.bak', $file);
            $totalChangedFiles++;
        }
    }
    $msg = "✅ All files reverted from backup.";
}

// Handle per-file RESET
if (isset($_GET['reset']) && $_GET['reset'] != '') {
    $target = basename($_GET['reset']);
    $filePath = $cssDir . $target;
    if (file_exists($filePath . '.bak')) {
        copy($filePath . '.bak', $filePath);
        $msg = "✅ <b>$target</b> reverted from backup.";
    }
}

// Handle SAVE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['color_map']) && isset($_POST['file'])) {
    $file = $_POST['file'];
    $filePath = realpath($file);
    if (strpos($filePath, realpath($cssDir)) !== 0) die("Invalid access");

    if (!file_exists($filePath . '.bak')) {
        copy($filePath, $filePath . '.bak');
    }

    $contents = file_get_contents($filePath);
    $changed = false;
    foreach ($_POST['color_map'] as $old => $new) {
        if (trim($old) !== trim($new)) {
            $contents = str_ireplace($old, $new, $contents);
            $changed = true;
        }
    }

    if ($changed) {
        file_put_contents($filePath, $contents);
        $msg = "✅ <b>" . basename($file) . "</b> updated successfully!";
        $totalChangedFiles++;
    }
}

function extractColors($content, &$rgbCount = 0) {
    $colors = [];
    preg_match_all('/#(?:[0-9a-fA-F]{3}){1,2}\b|rgba?\([^)]+\)/i', $content, $matches);
    foreach ($matches[0] as $color) {
        $colors[strtolower($color)] = true;
        if (stripos($color, 'rgb') === 0) {
            $rgbCount++;
        }
    }
    return array_keys($colors);
}

function convertToHex($color) {
    $color = trim(strtolower($color));
    if (preg_match('/^#([a-f0-9]{3,6})$/i', $color)) return $color;
    if (preg_match('/rgba?\((\d+),\s*(\d+),\s*(\d+)/', $color, $m)) {
        return sprintf("#%02x%02x%02x", $m[1], $m[2], $m[3]);
    }
    return '#000000';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>🎨 CSS Color Editor - Admin</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; background: #f8f9fa; }
        main { padding: 20px; max-width: 100%; width: 100%; box-sizing: border-box; }
        .stats { display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; justify-content: space-between; }
        .stat-box { background: #fff; padding: 15px 20px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 10px; flex: 1; min-width: 220px; }
        .stat-box i { font-size: 28px; color: #007bff; }
        .file-block { border: 1px solid #ddd; background: #fff; padding: 15px; margin-bottom: 25px; border-radius: 8px; box-shadow: 0 0 4px rgba(0,0,0,0.05); display: flex; justify-content: space-between; flex-wrap: wrap; }
        .file-block .left { flex: 1; }
        .file-block .right { flex: 1; max-width: 320px; text-align: right; font-size: 14px; color: #555; line-height: 1.6; }
        .color-row { display: flex; align-items: center; margin-bottom: 10px; gap: 12px; }
        .color-sample { width: 30px; height: 30px; border-radius: 4px; border: 1px solid #ccc; }
        input[type="color"] { width: 50px; height: 30px; }
        button, .btn-reset { padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn-reset-all {
            background: #dc3545;
            margin: 30px auto 40px auto;
            padding: 16px 0;
            font-size: 18px;
            width: 100%;
            max-width: 1000px;
            display: block;
            text-align: center;
        }
        .btn-reset:hover, .btn-reset-all:hover { background: #c82333; }
        h2 { margin-bottom: 10px; }
        .success { padding: 12px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 20px; }
        .pagination { margin-top: 30px; text-align: center; }
        .pagination a { margin: 0 5px; padding: 8px 12px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        .pagination a:hover { background: #0056b3; }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<main>
<h2>🎯 CSS Color Editor - Admin Panel</h2>
<?php if (isset($msg)) echo "<div class='success'>$msg</div>"; ?>
<div class="stats">
    <div class="stat-box"><i class="material-icons">layers</i> <div><b>Total Files:</b><br><?= $totalFiles ?></div></div>
    <div class="stat-box"><i class="material-icons">palette</i> <div><b>Total Colors:</b><br><?php
        $colorSet = [];
        foreach ($cssFiles as $file) {
            $content = file_get_contents($file);
            $colors = extractColors($content);
            foreach ($colors as $c) $colorSet[strtolower($c)] = true;
        }
        echo count($colorSet);
    ?></div></div>
    <div class="stat-box"><i class="material-icons">edit</i> <div><b>Total Changes:</b><br><?= $totalChangedFiles ?></div></div>
    <div class="stat-box"><i class="material-icons">gradient</i> <div><b>Total RGB:</b><br><?php
        $rgbCount = 0;
        foreach ($cssFiles as $file) {
            $content = file_get_contents($file);
            extractColors($content, $rgbCount);
        }
        echo $rgbCount;
    ?></div></div>
</div>
<a class="btn-reset btn-reset-all" href="?reset_all=1" onclick="return confirm('Revert all CSS files from backup?')">♻️ Reset All</a>

<?php foreach ($pagedFiles as $file): ?>
    <?php
    $content = file_get_contents($file);
    $colors = extractColors($content, $rgbCount = 0);
    $lastModified = date("d-M-Y h:i A", filemtime($file));
    $basename = basename($file);
    $usage = isset($fileUsageMap[$basename]) ? $fileUsageMap[$basename] : 'Unknown';
    ?>
    <div class="file-block">
        <div class="left">
            <h3>🗂 <?= $basename ?></h3>
            <form method="post">
                <input type="hidden" name="file" value="<?= htmlspecialchars($file) ?>">
                <?php foreach ($colors as $color): ?>
                    <div class="color-row">
                        <div class="color-sample" style="background: <?= htmlspecialchars($color) ?>"></div>
                        <label><?= htmlspecialchars($color) ?></label>
                        <input type="color" name="color_map[<?= htmlspecialchars($color) ?>]" value="<?= htmlspecialchars(convertToHex($color)) ?>">
                    </div>
                <?php endforeach; ?>
                <button type="submit">💾 Save Changes</button>
                <a class="btn-reset" href="?reset=<?= urlencode($basename) ?>&page=<?= $page ?>" onclick="return confirm('Reset this file?')">♻️ Revert</a>
            </form>
        </div>
        <div class="right">
            📝 Usage: <?= $usage ?><br>
            🎨 Total Colors: <?= count($colors) ?><br>
            🎯 Unique RGB: <?= $rgbCount ?><br>
            📅 Last Modified: <?= $lastModified ?>
        </div>
    </div>
<?php endforeach; ?>

<div class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>"<?= $i === $page ? ' style="font-weight:bold;background:#0056b3;"' : '' ?>><?= $i ?></a>
    <?php endfor; ?>
</div>
</main>
</body>
</html>
