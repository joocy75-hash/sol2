<?php
session_start();
if ($_SESSION['unohs'] == null) {
    header("location:index.php?msg=unauthorized");
}

include "conn.php"; // Database connection

$updateSuccess = false;
$updateError = false;

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskAmounts = $_POST['taskAmount'];
    $rechargeAmounts = $_POST['rechargeAmount'];
    $taskPeoples = $_POST['taskPeople'];
    $ids = $_POST['id'];  // IDs to identify which row to update
    
    for ($i = 0; $i < count($ids); $i++) {
        $taskAmount = $taskAmounts[$i];
        $rechargeAmount = $rechargeAmounts[$i];
        $taskPeople = $taskPeoples[$i];
        $id = $ids[$i];

        // Fetch the existing data to check if there's an actual change
        $query = "SELECT taskAmount, rechargeAmount, taskPeople FROM tbl_invitebonus WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row && ($taskAmount != $row['taskAmount'] || $rechargeAmount != $row['rechargeAmount'] || $taskPeople != $row['taskPeople'])) {
            // Only update if values are changed
            $sql = "UPDATE tbl_invitebonus SET taskAmount = ?, rechargeAmount = ?, taskPeople = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("diii", $taskAmount, $rechargeAmount, $taskPeople, $id);

            if ($stmt->execute()) {
                $updateSuccess = true;
            } else {
                $updateError = true;
            }
            $stmt->close();
        }
    }
}

// Fetch all data to display in the table
$result = $conn->query("SELECT * FROM tbl_invitebonus");
?>


<?php include 'header.php'; ?>

      
      <div class="container mt-4">
    <h2 class="text-center">Edit Invite Bonuses</h2>

    <!-- Show success or error message -->
    <?php if ($updateSuccess): ?>
        <div class="alert alert-success text-center">Update done successfully!</div>
    <?php elseif ($updateError): ?>
        <div class="alert alert-danger text-center">Update error occurred!</div>
    <?php endif; ?>

    <form method="POST">
        <div class="table-responsive">
            <table class="table table-striped table-hover text-center align-middle w-100">
                <thead class="table-primary">
                    <tr>
                        <th>Task ID 🆔</th>
                        <th>Task Amount ৳</th>
                        <th>Recharge Amount ৳</th>
                        <th>Task People ➤</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <input type="hidden" name="id[]" value="<?php echo $row['id']; ?>">
                                <strong><?php echo $row['taskID']; ?></strong>
                            </td>
                            <td>
                                <input type="number" name="taskAmount[]" 
                                       class="form-control text-center fw-bold border-primary"
                                       value="<?php echo $row['taskAmount']; ?>" min="0" required>
                            </td>
                            <td>
                                <input type="number" name="rechargeAmount[]" 
                                       class="form-control text-center fw-bold border-success"
                                       value="<?php echo $row['rechargeAmount']; ?>" min="0" required>
                            </td>
                            <td>
                                <input type="number" name="taskPeople[]" 
                                       class="form-control text-center fw-bold border-info"
                                       value="<?php echo $row['taskPeople']; ?>" min="0" required>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div class="text-center mt-4">
            <button type="submit" class="btn btn-lg btn-success w-100">Update All</button>
        </div>
    </form>
</div>


</html>

</body>
</html>


</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
