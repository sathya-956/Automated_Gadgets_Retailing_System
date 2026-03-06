<?php
$conn = new mysqli("localhost", "root", "", "electronics_db");

// 1. Handle Placing a New Order
if(isset($_POST['place_order'])) {
    $p_id = $_POST['product_id'];
    $qty = $_POST['qty'];
    $supplier = $_POST['supplier'];
    $phone = $_POST['phone']; 
    
    $conn->query("INSERT INTO orders (supplier_name, supplier_phone, product_id, quantity) 
                  VALUES ('$supplier', '$phone', $p_id, $qty)");
    header("Location: orders.php");
}

// 2. Handle Receiving Stock
if(isset($_GET['receive_id'])) {
    $order_id = $_GET['receive_id'];
    $order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
    
    if($order['status'] == 'Pending') {
        $conn->query("UPDATE inventory SET stock_count = stock_count + {$order['quantity']} WHERE id = {$order['product_id']}");
        $conn->query("UPDATE orders SET status = 'Received' WHERE id = $order_id");
        echo "<script>alert('Stock Received!'); window.location='orders.php';</script>";
    }
}

// 3. Handle Deleting/Cancelling an Order
if(isset($_GET['delete_id'])) {
    $del_id = $_GET['delete_id'];
    $conn->query("DELETE FROM orders WHERE id = $del_id");
    echo "<script>alert('Record Deleted!'); window.location='orders.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ventorie | Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --accent: #c6f021; --sidebar: #000; --bg: #f4f7f6; --text: #8e8e93; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; display: flex; }
        .sidebar { width: 250px; background: var(--sidebar); color: white; height: 100vh; padding: 25px; position: fixed; }
        .sidebar h2 { color: var(--accent); }
        .nav-item { padding: 12px 0; color: var(--text); text-decoration: none; display: flex; align-items: center; font-size: 14px; }
        .nav-item.active { color: white; }
        .main { margin-left: 250px; flex: 1; padding: 40px; }
        .card { background: white; padding: 25px; border-radius: 15px; border: 1px solid #eee; margin-bottom: 20px; }
        input, select, button { width: 100%; padding: 12px; margin: 8px 0; border-radius: 8px; border: 1px solid #ddd; font-size: 14px; }
        .btn-order { background: #000; color: #fff; cursor: pointer; border: none; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; font-size: 14px; }
        
        /* Buttons and Status */
        .status-pending { color: #f39c12; font-weight: bold; }
        .status-received { color: #27ae60; font-weight: bold; }
        .btn-receive { background: var(--accent); color: #000; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 12px; }
        
        /* Redesigning the Delete Link into a Button */
        .btn-delete { 
            color: #ff4d4d; 
            text-decoration: none; 
            font-weight: bold; 
            font-size: 12px; 
            padding: 5px 10px; 
            border: 1px solid #ff4d4d; 
            border-radius: 4px;
            transition: 0.3s;
        }
        .btn-delete:hover { background: #ff4d4d; color: white; }
        
        /* Flexbox for the Action column */
        .action-cell { display: flex; align-items: center; gap: 10px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Ventorie</h2>
    <a href="index.php" class="nav-item"><i class="fa-solid fa-gauge"></i> Dashboard</a>
    <a href="billing.php" class="nav-item"><i class="fa-solid fa-file-invoice-dollar"></i> Billing</a>
    <a href="orders.php" class="nav-item active"><i class="fa-solid fa-cart-flatbed"></i> Orders</a>
</div>

<div class="main">
    <div class="card">
        <h3>Restock Order</h3>
        <form method="POST">
            <input type="text" name="supplier" placeholder="Supplier Name" required>
            <input type="text" name="phone" placeholder="Supplier Contact Number" required>
            <select name="product_id" required>
                <option value="">Select Product to Restock</option>
                <?php
                $res = $conn->query("SELECT id, gadget_name FROM inventory");
                while($p = $res->fetch_assoc()) {
                    echo "<option value='{$p['id']}'>{$p['gadget_name']}</option>";
                }
                ?>
            </select>
            <input type="number" name="qty" placeholder="Quantity" min="1" required>
            <button type="submit" name="place_order" class="btn-order">Place Order</button>
        </form>
    </div>

    <div class="card">
        <h3>Order History</h3>
        <table>
            <thead>
                <tr><th>Supplier & Contact</th><th>Product</th><th>Qty</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php
                $orders = $conn->query("SELECT orders.*, inventory.gadget_name FROM orders JOIN inventory ON orders.product_id = inventory.id ORDER BY id DESC");
                while($o = $orders->fetch_assoc()) {
                    $statusClass = ($o['status'] == 'Pending') ? 'status-pending' : 'status-received';
                    $phone = $o['supplier_phone'] ?? 'N/A';

                    echo "<tr>
                        <td><strong>{$o['supplier_name']}</strong><br><small style='color:#666'>$phone</small></td>
                        <td>{$o['gadget_name']}</td>
                        <td>{$o['quantity']}</td>
                        <td class='$statusClass'>{$o['status']}</td>
                        <td class='action-cell'>";
                    
                    if($o['status'] == 'Pending') {
                        echo "<a href='orders.php?receive_id={$o['id']}' class='btn-receive'>Mark Received</a>";
                    } else {
                        echo "<span style='color:green; font-weight:bold;'>✅ Received</span>";
                    }
                    
                    // The Delete/Cancel Button - Now with a border and hover effect
                    echo "<a href='orders.php?delete_id={$o['id']}' class='btn-delete' onclick='return confirm(\"Are you sure you want to delete this record?\")'><i class='fa-solid fa-trash-can'></i> Delete</a>";
                    
                    echo "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>