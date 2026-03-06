<?php
$conn = new mysqli("localhost", "root", "", "electronics_db");

// Handle Status Updates
if(isset($_GET['update_id']) && isset($_GET['status'])) {
    $id = $_GET['update_id'];
    $status = $_GET['status'];
    $conn->query("UPDATE deliveries SET status = '$status' WHERE id = $id");
    header("Location: delivery.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daya Electronics| Delivery Management</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; display: flex; }
        .sidebar { width: 250px; background: #1a2226; color: white; height: 100vh; padding: 20px; position: fixed; }
        .main { margin-left: 250px; flex: 1; padding: 40px; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; padding: 12px; border-bottom: 2px solid #eee; color: #666; font-size: 13px; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        .status-badge { padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; }
        .processing { background: #e3f2fd; color: #1976d2; }
        .delivered { background: #e8f5e9; color: #2e7d32; }
        .btn { text-decoration: none; padding: 5px 10px; background: #eee; color: #333; border-radius: 4px; font-size: 12px; margin-right: 5px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2 style="color: #c6f021;">Ventorie</h2>
    <p><a href="billing.php" style="color: #999; text-decoration: none;">← Back to Billing</a></p>
</div>

<div class="main">
    <h1>Delivery Management</h1>
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Bill ID</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = $conn->query("SELECT * FROM deliveries ORDER BY id DESC");
                while($row = $res->fetch_assoc()) {
                    $class = ($row['status'] == 'Delivered') ? 'delivered' : 'processing';
                    echo "<tr>
                        <td>#{$row['bill_id']}</td>
                        <td><strong>{$row['customer_name']}</strong></td>
                        <td><span class='status-badge $class'>{$row['status']}</span></td>
                        <td>
                            <a href='delivery.php?update_id={$row['id']}&status=Out for Delivery' class='btn'>Ship</a>
                            <a href='delivery.php?update_id={$row['id']}&status=Delivered' class='btn' style='background:#c6f021'>Complete</a>
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>