let lastTransactionId = 0;
let newTransactionCount = 0;

function fetchTransactions() {
    console.log("Fetching transactions...");

    $.ajax({
        url: "fetch_transactions.php",
        type: "GET",
        dataType: "json",
        success: function(data) {
            console.log("Received Data:", data);

            if (!Array.isArray(data) || data.length === 0) {
                console.warn("⚠️ No transactions found!");
                $("#transaction-content").html("<p>No recent transactions</p>");
                return;
            }

            let content = "";
            let hasNewTransaction = false;

            data.forEach(transaction => {
                let user_id = transaction.user_id || "Unknown";
                let mobile = transaction.mobile || "No Mobile";
                let amount = transaction.amount !== undefined ? `৳${transaction.amount}` : "৳0";
                let order_id = transaction.order_id || "N/A";
                let gateway = transaction.gateway || transaction.bank_account || "Unknown";
                let type = transaction.type || "Unknown";
                let status = transaction.status || "Pending";
                let created_at = transaction.created_at || "Unknown Date";

                // ✅ Agar transaction naya hai tabhi dikhayenge
                if (order_id > lastTransactionId) {
                    hasNewTransaction = true;
                    newTransactionCount++;
                    lastTransactionId = order_id;
                }

                let transactionIcon = type === "withdrawal" ? "fa-money" : "fa-credit-card";
                let statusClass = type === "withdrawal" ? "withdrawal-item" : "recharge-item";

                content += `
                    <div class="transaction-item ${statusClass}" style="padding: 10px; border-bottom: 1px solid #ddd;">
                        <i class="fa ${transactionIcon}" style="margin-right: 5px;"></i> <b>${type.toUpperCase()}</b>
                        <br>👤 <b>User:</b> ${user_id} | 📞 <b>Mobile:</b> ${mobile}
                        <br>💰 <b>Amount:</b> ${amount} (<span style="color: red;">${status}</span>)
                        <br>🆔 <b>Order:</b> ${order_id} | 🔄 <b>Gateway:</b> ${gateway}
                        <br><small>📅 <b>${created_at}</b></small>
                    </div>`;
            });

            console.log("Transactions Added to UI:", content);

            // ✅ Transactions panel ko update karo
            if (content.trim() !== "") {
                $("#transaction-content").html(content);
            } else {
                $("#transaction-content").html("<p>No recent transactions</p>");
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", error);
            $("#transaction-content").html(`<p style="color: red;">AJAX Error: ${error}</p>`);
        }
    });
}

// ✅ Floating Transaction Icon Pe New Alert
$(document).ready(function () {
    $("#floating-transaction-icon").append('<span id="new-transaction-alert" style="display:none; background:red; color:white; padding:3px 6px; border-radius:50%; position:absolute; top:0; right:0; font-size:12px;">0</span>');
});

// ✅ Click karne pe count reset
$("#floating-transaction-icon").click(function () {
    newTransactionCount = 0;
    $("#new-transaction-alert").hide();
});

// **Every 5 seconds update transactions**
setInterval(fetchTransactions, 5000);

fetchTransactions(); // ✅ Load initially
