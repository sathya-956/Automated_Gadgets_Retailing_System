<?php
$conn = new mysqli("localhost", "root", "", "electronics_db");

// Handle adding a new category
if(isset($_POST['add_cat'])) {
    $name = $_POST['cat_name'];
    $conn->query("INSERT INTO categories (name) VALUES ('$name')");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories | Ventorie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f7f6; display: flex; justify-content: center; padding: 50px; }
        .box { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 450px; }
        h2 { color: #000; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 10px; box-sizing: border-box; }
        button { background: #c6f021; width: 100%; padding: 12px; border: none; border-radius: 10px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        button:hover { background: #b4db1e; }
        table { width: 100%; margin-top: 30px; border-collapse: collapse; }
        td { padding: 12px; border-bottom: 1px solid #f2f2f2; color: #333; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #888; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
    <div class="box">
        <h2><i class="fa-solid fa-layer-group"></i> Categories</h2>
        
        <form method="POST">
            <input type="text" name="cat_name" placeholder="New Category Name..." required>
            <button type="submit" name="add_cat">Add Category</button>
        </form>

        <table>
            <thead>
                <tr><th style="text-align:left; color:#888; font-size:12px;">NAME</th></tr>
            </thead>
            <tbody>
                <?php
                $res = $conn->query("SELECT * FROM categories");
                while($row = $res->fetch_assoc()) {
                    echo "<tr><td><i class='fa-solid fa-tag' style='color:#c6f021; margin-right:10px;'></i> {$row['name']}</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <a href="index.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>
</html>