<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}
date_default_timezone_set("Asia/Dhaka");

include 'conn.php';
?>





    <style>
    
    .header h1 {
    font-size: 22px;
}

.stat-box h3 {
    font-size: 18px;
}

table th,
table td {
    font-size: 14px;
}

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1100px;
            margin: 30px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .header {
            background-color: #ff0000;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
            margin-bottom: 20px;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .stat-box {
            flex: 1 1 30%;
            background: #fafafa;
            margin: 10px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .material-icons {
            font-size: 48px;
            color: #9481ff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #9481ff;
            color: white;
        }
        button.ban-btn {
            background: #ff4d4d;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
        button.ban-btn:hover {
            background: #e60000;
        }
    </style>
</head>


<?php include 'header.php'; ?>
<body>
<div class="container">
    <div class="header">
        <h1>Duplicate IP Checker - Admin Panel</h1>
    </div>

    <?php
    // Count Total Same IP Users
    $sameIpQuery = "SELECT COUNT(DISTINCT ishonup) AS total FROM shonu_subjects GROUP BY ishonup HAVING COUNT(*) > 1";
    $sameIpResult = $conn->query($sameIpQuery);
    $totalSameIpUsers = $sameIpResult->num_rows;

    // Total Banned Users
    $bannedQuery = "SELECT COUNT(*) AS total FROM shonu_subjects WHERE status = 0";
    $bannedResult = $conn->query($bannedQuery);
    $bannedRow = $bannedResult->fetch_assoc();
    $totalBannedUsers = $bannedRow['total'];

    // Total Authentic Users
    $authenticQuery = "SELECT COUNT(*) AS total FROM shonu_subjects WHERE status = 1";
    $authenticResult = $conn->query($authenticQuery);
    $authenticRow = $authenticResult->fetch_assoc();
    $totalAuthenticUsers = $authenticRow['total'];
    ?>

    <div class="stats">
        <div class="stat-box">
            <span class="material-icons">groups</span>
            <h3><?php echo $totalSameIpUsers; ?> Same IP Users</h3>
        </div>
        <div class="stat-box">
            <span class="material-icons">person_off</span>
            <h3><?php echo $totalBannedUsers; ?> Banned Users</h3>
        </div>
        <div class="stat-box">
            <span class="material-icons">verified_user</span>
            <h3><?php echo $totalAuthenticUsers; ?> Authentic Users</h3>
        </div>
    </div>

    <h2 style="text-align:center; margin-bottom:20px;">Same IP Users List</h2>

    <table>
        <thead>
            <tr>
                <th>IP Address</th>
                <th>User ID</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php
        // Fetch duplicate IPs
        $duplicateIPsQuery = "SELECT ishonup FROM shonu_subjects GROUP BY ishonup HAVING COUNT(ishonup) > 1";
        $result = $conn->query($duplicateIPsQuery);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $duplicateIP = $row['ishonup'];

                // Fetch users with this duplicate IP
                $fetchUsersQuery = "SELECT id, status FROM shonu_subjects WHERE ishonup = ?";
                $stmt = $conn->prepare($fetchUsersQuery);
                $stmt->bind_param("s", $duplicateIP);
                $stmt->execute();
                $usersResult = $stmt->get_result();

                while ($user = $usersResult->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($duplicateIP) . "</td>";
                    echo "<td>" . $user['id'] . "</td>";
                    echo "<td>" . ($user['status'] == 0 ? 'Banned' : 'Active') . "</td>";
                    echo "<td>";
                    if ($user['status'] != 0) {
                        echo "<form method='POST' style='display:inline;'>";
                        echo "<input type='hidden' name='ban_id' value='" . $user['id'] . "'>";
                        echo "<button type='submit' class='ban-btn'>Ban</button>";
                        echo "</form>";
                    } else {
                        echo "-";
                    }
                    echo "</td>";
                    echo "</tr>";
                }

                $stmt->close();
            }
        } else {
            echo "<tr><td colspan='4'>No Duplicate IPs Found</td></tr>";
        }
        ?>
        </tbody>
    </table>

    <?php
    // Ban user if Ban button clicked
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ban_id'])) {
        $banId = intval($_POST['ban_id']);

        $banQuery = "UPDATE shonu_subjects SET status = 0 WHERE id = ?";
        $stmtBan = $conn->prepare($banQuery);
        $stmtBan->bind_param("i", $banId);

        if ($stmtBan->execute()) {
            echo "<script>alert('User ID $banId banned successfully.'); window.location.href=window.location.href;</script>";
        } else {
            echo "<script>alert('Failed to ban user.');</script>";
        }

        $stmtBan->close();
    }

    $conn->close();
    ?>
</div>
</body>
</html>
