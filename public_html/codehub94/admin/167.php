<?php
// db_exporter_gz.php

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);
ini_set('memory_limit', '1024M');

// --- User Configuration ---
$host = 'localhost';     // Database Host
$user = 'u209477126_sol0203';           // Database Username
$pass = 'UP209477126_sol0203';               // Database Password
$dbname = 'u209477126_sol0203';         // Database Name

// Connect to MySQL
$mysqli = @new mysqli($host, $user, $pass, $dbname);

if ($mysqli->connect_error) {
    die('Connection Failed: ' . $mysqli->connect_error);
}

// Prepare compressed file
$backup_file_name = 'backup_' . $dbname . '_' . date('Y-m-d_H-i-s') . '.sql.gz';
$gz = gzopen($backup_file_name, 'w9'); // 9 = maximum compression

if (!$gz) {
    die('Failed to create compressed backup file.');
}

// Get all tables
$tables = [];
$result = $mysqli->query('SHOW TABLES');
if ($result) {
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
} else {
    die('Error fetching tables: ' . $mysqli->error);
}

// Start backup
gzwrite($gz, "-- Database Backup\n");
gzwrite($gz, "-- Database: `$dbname`\n");
gzwrite($gz, "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n");

foreach ($tables as $table) {
    // Export CREATE TABLE
    $createTableResult = $mysqli->query("SHOW CREATE TABLE `$table`");
    if ($createTableResult) {
        $createTableRow = $createTableResult->fetch_assoc();
        gzwrite($gz, "\n\n" . $createTableRow['Create Table'] . ";\n\n");
    }

    // Export table data
    $rowsResult = $mysqli->query("SELECT * FROM `$table`");

    if ($rowsResult && $rowsResult->num_rows > 0) {
        while ($row = $rowsResult->fetch_assoc()) {
            $columns = array_keys($row);
            $escapedValues = array_map(function($value) use ($mysqli) {
                if (is_null($value)) return 'NULL';
                return "'" . $mysqli->real_escape_string($value) . "'";
            }, array_values($row));

            $insertSQL = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $escapedValues) . ");\n";
            gzwrite($gz, $insertSQL);
        }
    }
}

gzclose($gz);
$mysqli->close();

echo "Database export completed successfully.<br>";
echo "Compressed SQL file created: <strong>$backup_file_name</strong>";
?>