<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli("localhost", "root", "", "electronics_db");

if (isset($_POST['save'])) {
    $name = $_POST['n']; 
    $s = $_POST['s']; 
    $p = $_POST['p'];
    $cat_id = $_POST['cat_id'];
    $sup_id = $_POST['sup_id']; // Step 2: Ensure this is captured

    // Step 1: Ensure your database has 5 columns to match this
    $stmt = $conn->prepare("INSERT INTO inventory (gadget_name, stock_count, price, category_id, supplier_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sidii", $name, $s, $p, $cat_id, $sup_id);
    
    if($stmt->execute()) {
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Product | Ventorie</title>
    <style>
        :root { --accent: #c6f021; --dark: #1a1a1a; }
        body { font-family: 'Segoe UI', sans-serif; background: #000; color: #fff; display: flex; justify-content: center; padding-top: 30px; }
        .box { background: var(--dark); padding: 30px; border-radius: 20px; width: 400px; border: 1px solid #333; }
        h2 { color: var(--accent); margin-top: 0; }
        label { display: block; font-size: 11px; color: #888; text-transform: uppercase; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 10px; border: 1px solid #333; background: #222; color: #fff; box-sizing: border-box; }
        button { width: 100%; padding: 15px; background: var(--accent); color: #000; font-weight: bold; border-radius: 10px; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Add New Item</h2>
        <form method="POST">
            <label>Gadget Name</label>
            <input type="text" name="n" required>
            
            <label>Category</label>
            <select name="cat_id" required>
                <?php
                $cats = $conn->query("SELECT * FROM categories");
                while($c = $cats->fetch_assoc()) { echo "<option value='{$c['id']}'>{$c['name']}</option>"; }
                ?>
            </select>

            <label>Supplier</label>
            <select name="sup_id" required>
                <?php
                $sups = $conn->query("SELECT * FROM suppliers");
                if($sups->num_rows > 0) {
                    while($s = $sups->fetch_assoc()) {
                        echo "<option value='{$s['id']}'>{$s['supplier_name']}</option>";
                    }
                } else {
                    echo "<option value='1'>Default Supplier</option>";
                }
                ?>
            </select>

            <div style="display:flex; gap:15px;">
                <div style="flex:1;"><label>Stock</label><input type="number" name="s" required></div>
                <div style="flex:1;"><label>Price (₹)</label><input type="number" step="0.01" name="p" required></div>
            </div>

            <button type="submit" name="save">Save Product</button>
            <div style="text-align:center; margin-top:15px;"><a href="index.php" style="color:#666; text-decoration:none;">← Back</a></div>
        </form>
    </div>
</body>
</html>