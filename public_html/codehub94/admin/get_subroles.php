<?php
include("conn.php");
$module = isset($_GET['module']) ? $_GET['module'] : '';

if ($module != '') {
    $query = mysqli_query($conn, "SELECT submodule FROM navigation_structure WHERE module = '$module'");
    while ($row = mysqli_fetch_assoc($query)) {
        $sub = htmlspecialchars($row['submodule']);
        echo "<label><input type='checkbox' name='subroles[$module][]' value='$sub'> $sub</label><br>";
    }
}
?>
