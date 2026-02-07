<?php
session_start();
if ($_SESSION['unohs'] == null) {
    header("location:index.php?msg=unauthorized");
}
include("conn.php");
?>


<?php include 'header.php'; ?>

      <div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-sm-12 mb-4 mb-xl-0">
          <h4 class="font-weight-bold text-dark">Deposite Problem</h4>
          <div class="row">
            <div class="col-sm-12">
              <div class="table-responsive">
                <table id="yourTable" class="table table-striped">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>User ID</th>
                      <th>Order No</th>
                      <th>Amount</th>
                      <th>UTR</th>
                      <th>images</th>
                      <th>images2</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $sql = "SELECT id, userid, deposit_order_no,image_upload,file_upload, order_amount, utr, remarks FROM your_table WHERE prob = 'Deposit Not Receive' AND status = 2";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row["id"] . "</td>";
                            echo "<td>" . $row["userid"] . "</td>";
                            echo "<td>" . $row["deposit_order_no"] . "</td>";
                            echo "<td>" . $row["order_amount"] . "</td>";
                            echo "<td>" . $row["utr"] . "</td>";
                            echo "<td><a href='https://24game.chat/uploads/" . $row["file_upload"] . "'>" . $row["file_upload"] . "</a></td>";
                            echo "<td><a href='https://24game.chat/uploads/" . $row["image_upload"] . "'>" . $row["image_upload"] . "</a></td>";
                            echo "<td><button class='btn btn-primary edit-btn' data-id='" . $row["id"] . "' data-remarks='" . $row["remarks"] . "'>Do Responce</button></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No results found.</td></tr>";
                    }

                    $conn->close();
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal for editing remarks -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Send Meassge</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form id="editForm">
                  <input type="hidden" id="editId" name="id">
                  <div class="mb-3">
                    <label for="editRemarks" class="form-label">Remarks</label>
                    <textarea class="form-control" id="editRemarks" name="remarks" rows="3"></textarea>
                  </div>
                  <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
           
         

        <!-- Footer -->
<?php
// footer.php
?>
<footer style="
  width: 100%;
  background: linear-gradient(90deg, #1f4bb9, #0d0e37, #0016b5);
  color: white;
  padding: 12px 0;
  text-align: center;
  font-size: 14px;
  font-weight: 500;
  position: relative;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 999;
  font-family: 'Segoe UI', sans-serif;
  animation: rgbflow 8s ease infinite;
  background-size: 300% 300%;
  border-top: 2px solid rgba(255, 255, 255, 0.15);
">
  © <?= date('Y') ?> Sol-0203 Private Limited. All rights reserved. | <span style="color: #e0ffe0;">Patented & Protected</span>.
</footer>


  
  <script>
    $(document).ready(function () {
      $('#yourTable').DataTable();

      // Open modal and populate data
      $('.edit-btn').click(function () {
        const id = $(this).data('id');
        const remarks = $(this).data('remarks');
        $('#editId').val(id);
        $('#editRemarks').val(remarks);
        $('#editModal').modal('show');
      });

      // Submit form to update remarks
      $('#editForm').submit(function (e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.post('update_remarks.php', formData, function (response) {
          alert(response.message);
          if (response.success) {
            location.reload();
          }
        }, 'json');
      });
    });
  </script>
</body>

</html>