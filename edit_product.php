<?php
$conn = new mysqli("localhost", "root", "", "electronics_db");

// 1. Get the ID from the URL (the ?id= part)
if (!isset($_GET['id'])) { header("Location: index.php"); exit(); }
$id = $_GET['id'];

// 2. Handle the Update when "Save Changes" is clicked
if (isset($_POST['update'])) {
    $name = $_POST['gadget_name'];
    $price = $_POST['price'];
    $stock = $_POST['stock_count'];
    $cat = $_POST['category_id'];
    $sup = $_POST['supplier_id'];

    $conn->query("UPDATE inventory SET gadget_name='$name', price='$price', stock_count='$stock', category_id='$cat', supplier_id='$sup' WHERE id=$id");
    header("Location: index.php"); // Go back to dashboard after saving
}

// 3. Fetch current product data to pre-fill the form
$product = $conn->query("SELECT * FROM inventory WHERE id=$id")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product | Ventorie</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f7f6; display: flex; justify-content: center; padding: 50px; }
        .card { background: white; padding: 30px; border-radius: 20px; width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; font-size: 20px; }
        label { display: block; margin: 15px 0 5px; font-size: 12px; font-weight: bold; color: #888; text-transform: uppercase; }
        input, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; box-sizing: border-box; outline: none; }
        .save-btn { background: #000; color: #c6f021; border: none; padding: 15px; width: 100%; border-radius: 10px; margin-top: 25px; font-weight: bold; cursor: pointer; }
        .cancel-link { display: block; text-align: center; margin-top: 15px; color: #ff4d4d; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>

<div class="card">
    <h2>Edit Product</h2>
    <form method="POST">
        <label>Product Name</label>
        <input type="text" name="gadget_name" value="<?php echo htmlspecialchars($product['gadget_name']); ?>" required>

        <label>Price (₹)</label>
        <input type="number" name="price" value="<?php echo $product['price']; ?>" required>

        <label>Stock Count</label>
        <input type="number" name="stock_count" value="<?php echo $product['stock_count']; ?>" required>

        <label>Category</label>
        <select name="category_id">
            <?php
            $cats = $conn->query("SELECT * FROM categories");
            while($c = $cats->fetch_assoc()) {
                $selected = ($c['id'] == $product['category_id']) ? "selected" : "";
                echo "<option value='{$c['id']}' $selected>{$c['name']}</option>";
            }
            ?>
        </select>

        <label>Supplier</label>
        <select name="supplier_id">
            <?php
            $sups = $conn->query("SELECT * FROM suppliers");
            while($s = $sups->fetch_assoc()) {
                $selected = ($s['id'] == $product['supplier_id']) ? "selected" : "";
                echo "<option value='{$s['id']}' $selected>{$s['supplier_name']}</option>";
            }
            ?>
        </select>

        <button type="submit" name="update" class="save-btn">Save Changes</button>
        <a href="index.php" class="cancel-link">Cancel</a>
    </form>
</div>

</body>
</html>