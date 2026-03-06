<?php
$conn = new mysqli("localhost", "root", "", "electronics_db");

if(isset($_POST['update_all'])) {
    $name = $_POST['shop_name'];
    $addr = $_POST['shop_address'];
    $phone = $_POST['shop_contact'];
    $user = $_POST['owner_username'];
    $pass = $_POST['owner_password'];
    $tax = $_POST['tax_percent'];
    $limit = $_POST['low_stock_limit'];

    $sql = "UPDATE settings SET 
            shop_name='$name', shop_address='$addr', shop_contact='$phone', 
            owner_username='$user', owner_password='$pass', 
            tax_percent='$tax', low_stock_limit='$limit' 
            WHERE id=1";
    
    if($conn->query($sql)) {
        echo "<script>alert('All Settings Synchronized!'); window.location='settings.php';</script>";
    }
}

$set = $conn->query("SELECT * FROM settings WHERE id=1")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ventorie | Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --accent: #c6f021; --dark: #000; --bg: #f4f7f6; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; display: flex; }
        .sidebar { width: 250px; background: var(--dark); color: white; height: 100vh; padding: 25px; position: fixed; }
        .main { margin-left: 250px; flex: 1; padding: 40px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        h3 { border-bottom: 2px solid var(--accent); display: inline-block; padding-bottom: 5px; margin-bottom: 20px; }
        label { display: block; font-size: 11px; font-weight: bold; color: #888; text-transform: uppercase; margin-top: 15px; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; margin-top: 5px; box-sizing: border-box; }
        .save-btn { background: var(--dark); color: var(--accent); padding: 15px 40px; border: none; border-radius: 10px; cursor: pointer; font-weight: bold; margin-top: 30px; font-size: 16px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2 style="color:var(--accent)">Ventorie</h2>
    <a href="index.php" style="color:#888; text-decoration:none; display:block; padding:10px 0;">Dashboard</a>
    <a href="billing.php" style="color:#888; text-decoration:none; display:block; padding:10px 0;">Billing</a>
    <a href="settings.php" style="color:white; text-decoration:none; display:block; padding:10px 0; font-weight:bold;">Settings</a>
</div>

<div class="main">
    <h1>System Configuration</h1>
    <form method="POST">
        <div class="grid">
            <div class="card">
                <h3>Store Identity</h3>
                <label>Store Name</label>
                <input type="text" name="shop_name" value="<?php echo $set['shop_name']; ?>">
                
                <label>Physical Address</label>
                <textarea name="shop_address"><?php echo $set['shop_address']; ?></textarea>
                
                <label>Contact Number</label>
                <input type="text" name="shop_contact" value="<?php echo $set['shop_contact']; ?>">
            </div>

            <div class="card">
                <h3>Business Rules</h3>
                <label>Tax Percentage (%)</label>
                <input type="number" step="0.01" name="tax_percent" value="<?php echo $set['tax_percent']; ?>">
                
                <label>Low Stock Warning Level</label>
                <input type="number" name="low_stock_limit" value="<?php echo $set['low_stock_limit']; ?>">
                
                <label>Admin Username</label>
                <input type="text" name="owner_username" value="<?php echo $set['owner_username']; ?>">
                
                <label>Admin Password</label>
                <input type="password" name="owner_password" value="<?php echo $set['owner_password']; ?>">
            </div>
        </div>
        
        <button type="submit" name="update_all" class="save-btn">Save All Configurations</button>
    </form>
</div>

</body>
</html>