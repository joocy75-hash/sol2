<?php
/********************************
 * CONFIGURATION
 ********************************/
$DB_HOST = 'localhost';
$DB_USER = 'u209477126_sol0203';
$DB_PASS = 'UP209477126_sol0203';
$DB_NAME = 'u209477126_sol0203';
$SQL_FILE = __DIR__ . "/SQL.sql";

/********************************
 * CONNECT TO DATABASE
 ********************************/
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

/********************************
 * CLEAR OLD DATABASE
 ********************************/
$res = $conn->query("SHOW TABLES");
while ($row = $res->fetch_array()) {
    $conn->query("DROP TABLE IF EXISTS `" . $row[0] . "`");
}

/********************************
 * CHECK SQL FILE
 ********************************/
if (!file_exists($SQL_FILE)) {
    die("SQL file (a.sql) not found!");
}

$file_size = filesize($SQL_FILE);
$handle = fopen($SQL_FILE, "r");

echo "<style>
#bar { width:450px; height:25px; border:1px solid #000; margin-bottom:10px; }
#fill { height:25px; width:0%; background:#4caf50; }
</style>";

echo "<div id='bar'><div id='fill'></div></div>";
echo "<p id='text'>Starting import...</p>";

ob_flush();
flush();

/********************************
 * FAST IMPORT — LINE BY LINE
 ********************************/
$query = "";
$read = 0;

while (!feof($handle)) {
    $line = fgets($handle);
    $read += strlen($line);

    // Ignore comments
    if (trim($line) == "" || strpos($line, "--") === 0 || strpos($line, "/*") === 0) {
        continue;
    }

    $query .= $line;

    // Execute when query ends
    if (substr(trim($line), -1) == ";") {
        $conn->query($query);
        $query = "";
    }

    // Progress update
    $percent = intval(($read / $file_size) * 100);

    echo "<script>
        document.getElementById('fill').style.width = '{$percent}%';
        document.getElementById('text').innerHTML = 'Importing... {$percent}%';
    </script>";
    ob_flush();
    flush();
}

fclose($handle);

echo "<script>
document.getElementById('text').innerHTML = 'Database Import Completed Successfully!';
</script>";

$conn->close();
?>
