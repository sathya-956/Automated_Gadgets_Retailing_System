<?php
$conn = new mysqli("localhost", "root", "", "electronics_db");

if(isset($_POST['add_supplier'])) {
    $name = $_POST['s_name'];
    $person = $_POST['s_person'];
    $phone = $_POST['s_phone'];
    
    $conn->query("INSERT INTO suppliers (supplier_name, contact_person, phone) VALUES ('$name', '$person', '$phone')");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Suppliers | Ventorie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --accent: #c6f021; --bg: #f4f7f6; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); padding: 40px; }
        .box { background: white; padding: 30px; border-radius: 20px; max-width: 800px; margin: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .form-row { display: flex; gap: 10px; margin-bottom: 25px; }
        input { flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none; }
        button { background: var(--accent); padding: 12px 25px; border: none; border-radius: 10px; font-weight: bold; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: #888; font-size: 12px; padding: 10px; border-bottom: 2px solid #f2f2f2; }
        td { padding: 15px 10px; border-bottom: 1px solid #f9f9f9; }
        .back-btn { display: inline-block; margin-bottom: 20px; text-decoration: none; color: #666; font-size: 14px; }
    </style>
</head>
<body>

    <a href="index.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>

    <div class="box">
        <h2><i class="fa-solid fa-truck-field"></i> Suppliers</h2>
        
        <form method="POST" class="form-row">
            <input type="text" name="s_name" placeholder="Supplier Company Name" required>
            <input type="text" name="s_person" placeholder="Contact Person">
            <input type="text" name="s_phone" placeholder="Phone Number">
            <button type="submit" name="add_supplier">Add Supplier</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>SUPPLIER NAME</th>
                    <th>CONTACT PERSON</th>
                    <th>PHONE</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = $conn->query("SELECT * FROM suppliers ORDER BY id DESC");
                while($row = $res->fetch_assoc()) {
                    echo "<tr>
                        <td><strong>{$row['supplier_name']}</strong></td>
                        <td>{$row['contact_person']}</td>
                        <td>{$row['phone']}</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>