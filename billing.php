<?php
$conn = new mysqli("localhost", "root", "", "electronics_db");

// 1. VOID LOGIC: Restores stock and removes the transaction
if(isset($_GET['void_id'])) {
    $void_id = $_GET['void_id'];
    $bill_data = $conn->query("SELECT product_name, quantity FROM billing WHERE id = $void_id")->fetch_assoc();
    
    if($bill_data) {
        $p_name = $bill_data['product_name'];
        $qty = $bill_data['quantity'];
        // Put stock back
        $conn->query("UPDATE inventory SET stock_count = stock_count + $qty WHERE gadget_name = '$p_name'");
        
        // ALSO REMOVE FROM DELIVERIES: If bill is voided, delivery should be canceled
        $conn->query("DELETE FROM deliveries WHERE bill_id = $void_id");
        
        // Delete bill
        $conn->query("DELETE FROM billing WHERE id = $void_id");
        echo "<script>alert('Bill Voided & Stock Restored'); window.location='billing.php';</script>";
    }
}

// 2. CREATE BILL LOGIC: Deducts stock and saves sale
if(isset($_POST['create_bill'])) {
    $product_id = $_POST['product_id'];
    $qty = $_POST['qty'];
    $customer = $_POST['customer_name'];

    $p_res = $conn->query("SELECT price, stock_count, gadget_name FROM inventory WHERE id = $product_id")->fetch_assoc();
    
    if($p_res['stock_count'] >= $qty) {
        $total = $p_res['price'] * $qty;
        $p_name = $p_res['gadget_name'];
        
        // A. Create the Bill
        $conn->query("INSERT INTO billing (customer_name, product_name, quantity, total_amount, bill_date) 
                      VALUES ('$customer', '$p_name', $qty, $total, NOW())");
        
        $bill_id = $conn->insert_id; // Capture the ID of the bill just made

        // B. THE CONNECTION: Automatically create a delivery record
        // This pushes the data to your delivery.php page instantly
        $conn->query("INSERT INTO deliveries (bill_id, customer_name, status) 
                      VALUES ('$bill_id', '$customer', 'Processing')");
        
        // C. Update Stock
        $conn->query("UPDATE inventory SET stock_count = stock_count - $qty WHERE id = $product_id");
        
        echo "<script>alert('Transaction Successful! Delivery has been scheduled.'); window.location='billing.php';</script>";
    } else {
        echo "<script>alert('Error: Out of Stock!');</script>";
    }
}

// 3. Stats for Header
$today = date('Y-m-d');
$sales_stats = $conn->query("SELECT SUM(total_amount) as daily_total, COUNT(*) as bill_count FROM billing WHERE DATE(bill_date) = '$today'")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dayananda Electronics| Billing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --accent: #c6f021; --sidebar: #000; --bg: #f4f7f6; --text: #8e8e93; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; display: flex; }
        .sidebar { width: 250px; background: var(--sidebar); color: white; height: 100vh; padding: 25px; position: fixed; }
        .sidebar h2 { color: var(--accent); }
        .nav-item { padding: 12px 0; color: var(--text); text-decoration: none; display: flex; align-items: center; font-size: 14px; }
        .nav-item.active { color: white; }
        .main { margin-left: 250px; flex: 1; padding: 40px; }
        .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .card { background: white; padding: 20px; border-radius: 15px; border: 1px solid #eee; }
        .graphics-grid { display: grid; grid-template-columns: 1fr 1.5fr; gap: 20px; }
        input, select, button { width: 100%; padding: 12px; margin: 8px 0; border-radius: 8px; border: 1px solid #ddd; font-size: 14px; }
        .submit-btn { background: #000; color: #fff; cursor: pointer; border: none; font-weight: bold; transition: 0.3s; }
        .submit-btn:hover { background: #333; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; font-size: 14px; }
        .btn-print { background: #2196f3; color: white; padding: 5px 12px; border-radius: 4px; text-decoration: none; margin-right: 10px; font-size: 12px; }
        .btn-void { background: #ff4d4d; color: white; padding: 5px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Dayananda Electronics</h2>
    <a href="index.php" class="nav-item"><i class="fa-solid fa-gauge"></i> Dashboard</a>
    <a href="billing.php" class="nav-item active"><i class="fa-solid fa-file-invoice-dollar"></i> Billing</a>
    <a href="delivery.php" class="nav-item"><i class="fa-solid fa-truck"></i> Delivery</a>
    <a href="login.php" class="nav-item" style="color:red; margin-top:30px;"><i class="fa-solid fa-power-off"></i> Logout</a>
</div>

<div class="main">
    <div class="stats-grid">
        <div class="card"><small>Daily Revenue</small><h2>₹<?php echo number_format($sales_stats['daily_total'] ?? 0, 2); ?></h2></div>
        <div class="card"><small>Bills Issued</small><h2><?php echo $sales_stats['bill_count'] ?? 0; ?></h2></div>
    </div>

    <div class="graphics-grid">
        <div class="card">
            <h3>New Invoice</h3>
            <form method="POST">
                <input type="text" name="customer_name" placeholder="Customer Name" required>
                <select name="product_id" required>
                    <option value="">Select Product</option>
                    <?php
                    $products = $conn->query("SELECT id, gadget_name, price, stock_count FROM inventory WHERE stock_count > 0");
                    while($p = $products->fetch_assoc()) {
                        echo "<option value='{$p['id']}'>{$p['gadget_name']} (₹{$p['price']})</option>";
                    }
                    ?>
                </select>
                <input type="number" name="qty" placeholder="Quantity" min="1" required>
                <button type="submit" name="create_bill" class="submit-btn">Complete Sale</button>
            </form>
        </div>

        <div class="card">
            <h3>Recent History</h3>
            <table>
                <thead><tr><th>Customer</th><th>Amount</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php
                    $bills = $conn->query("SELECT * FROM billing ORDER BY id DESC LIMIT 5");
                    while($b = $bills->fetch_assoc()) {
                        $c_name = addslashes($b['customer_name']);
                        $p_name = addslashes($b['product_name']);
                        $qty = $b['quantity'];
                        $total = number_format($b['total_amount'], 2);

                        echo "<tr>
                            <td><strong>{$b['customer_name']}</strong></td>
                            <td>₹{$total}</td>
                            <td>
                                <a href='javascript:void(0)' class='btn-print' onclick=\"printReceipt('$c_name', '$p_name', '$qty', '$total')\">Print</a>
                                <a href='billing.php?void_id={$b['id']}' class='btn-void' onclick='return confirm(\"Void Bill?\")'>Void</a>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function printReceipt(name, item, qty, total) {
    let w = window.open('', '', 'width=600,height=600');
    w.document.write(`
        <html>
        <head><title>Receipt - ${name}</title></head>
        <body style="font-family:sans-serif; text-align:center; padding:40px;">
            <div style="border:1px solid #000; padding:20px;">
                <h1 style="margin:0;">VENTORIE</h1>
                <p>Electronics Management System</p>
                <hr>
                <div style="text-align:left; margin:20px 0;">
                    <p><strong>Customer:</strong> ${name}</p>
                    <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                </div>
                <table style="width:100%; text-align:left; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:2px solid #000;">
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding:10px 0;">${item}</td>
                            <td>${qty}</td>
                            <td>₹${total}</td>
                        </tr>
                    </tbody>
                </table>
                <hr style="margin-top:20px;">
                <h2 style="text-align:right;">Total: ₹${total}</h2>
                <p style="margin-top:30px;">Thank you for your purchase!</p>
            </div>
        </body>
        </html>
    `);
    w.document.close();
    setTimeout(function() { w.print(); }, 500);
}
</script>
</body>
</html>