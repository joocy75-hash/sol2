<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}
date_default_timezone_set("Asia/Dhaka");

include 'conn.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Referral Tree - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .scroll-container {
            overflow-x: auto;
            padding: 30px;
            background-color: #f8f9fa;
            border: 1px solid #ccc;
            border-radius: 10px;
            position: relative;
        }
        .zoom-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        .zoom-controls {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 5px;
            opacity: 0;
            transition: 0.3s ease;
            z-index: 10;
            pointer-events: none;
        }
        .zoom-wrapper:hover .zoom-controls {
            opacity: 1;
            pointer-events: all;
        }
        .zoom-controls button {
            pointer-events: auto;
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: 0.2s ease-in-out;
        }
        .zoom-controls button:hover {
            background-color: #e2e2e2;
            transform: scale(1.1);
        }
        .zoom-label {
            font-size: 14px;
            margin-left: 8px;
            font-weight: 600;
            color: #444;
            line-height: 35px;
        }

        .tree {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            min-width: 800px;
        }

        .tree ul {
            padding-top: 20px;
            position: relative;
            display: flex;
            justify-content: center;
        }

        .tree li {
            list-style-type: none;
            text-align: center;
            margin: 0 15px;
            position: relative;
            padding: 20px 5px 0 5px;
        }

        .tree li::before, .tree li::after {
            content: '';
            position: absolute;
            top: 0;
            right: 50%;
            border-top: 2px solid #28a745;
            width: 50%;
            height: 20px;
        }

        .tree li::after {
            right: auto;
            left: 50%;
            border-left: 2px solid #28a745;
        }

        .tree li:only-child::before,
        .tree li:only-child::after {
            content: none;
        }

        .tree li:only-child {
            padding-top: 0;
        }

        .tree li .box {
            display: inline-block;
            border: 2px solid #28a745;
            padding: 10px 15px;
            border-radius: 10px;
            background: #e9fdf0;
            font-weight: 500;
            min-width: 160px;
        }

        .tree li .main-box {
            background-color: #28a745;
            color: white;
        }
    
    </style>
<?php include 'header.php'; ?>

<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Referral Tree</h4>
        <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    <form method="GET" class="row g-3 mb-3">
        <div class="col-md-4">
            <input type="text" name="uid" class="form-control" placeholder="Enter digit UID" required>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-warning">SHOW MY TREE</button>
        </div>
    </form>

    <?php
    if (isset($_GET['uid'])) {
        $uid = intval($_GET['uid']);
        $stmt = $conn->prepare("SELECT owncode FROM shonu_subjects WHERE id = ?");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $stmt->bind_result($owncode);
        $stmt->fetch();
        $stmt->close();

        if ($owncode) {
            echo "<div class='scroll-container'>";
            echo "<div class='zoom-wrapper'>";

            // Zoom buttons
            echo "
            <div class='zoom-controls'>
                <button onclick='zoomIn()' title='Zoom In'>+</button>
                <button onclick='zoomOut()' title='Zoom Out'>−</button>
                <button onclick='resetZoom()' title='Reset Zoom'>⟳</button>
                <div class='zoom-label' id='zoomLabel'>60%</div>
            </div>";

            // Tree start
            echo "<div id='zoomContainer'><div class='tree'>";
            echo "<ul><li><div class='box main-box'>UID:$uid</div></li></ul>";

            $levels = ['code', 'code1', 'code2', 'code3', 'code4', 'code5'];
            foreach ($levels as $level) {
                $query = "SELECT id, mobile FROM shonu_subjects WHERE $level = ?";
                $stmt2 = $conn->prepare($query);
                if ($stmt2) {
                    $stmt2->bind_param("s", $owncode);
                    $stmt2->execute();
                    $result = $stmt2->get_result();

                    if ($result->num_rows > 0) {
                        echo "<ul>";
                        while ($row = $result->fetch_assoc()) {
                            echo "<li><div class='box'>UID: {$row['id']}<br>Ph: {$row['mobile']}</div></li>";
                        }
                        echo "</ul>";
                    }
                    $stmt2->close();
                } else {
                    echo "<div class='text-danger'>Query failed for $level: " . $conn->error . "</div>";
                }
            }

            echo "</div></div></div></div>"; // Close tree, zoomContainer, zoomWrapper, scroll-container
        } else {
            echo "<div class='alert alert-danger'>UID not found.</div>";
        }
    }
    ?>
</div>

<script>
    let zoomLevel = 1;

    function zoomIn() {
        zoomLevel = Math.min(zoomLevel + 0.1, 2);
        applyZoom();
    }

    function zoomOut() {
        zoomLevel = Math.max(zoomLevel - 0.1, 0.3);
        applyZoom();
    }

    function resetZoom() {
        zoomLevel = 1;
        applyZoom();
    }

    function applyZoom() {
        const container = document.getElementById("zoomContainer");
        const label = document.getElementById("zoomLabel");
        container.style.transform = `scale(${zoomLevel})`;
        label.innerText = Math.round(zoomLevel * 60) + "%";
    }
    
    
</script>





</body>
</html>
