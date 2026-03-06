<?php
session_start();
if(!isset($_SESSION['owner'])) { header("Location: login.php"); exit(); }

$conn = new mysqli("localhost", "root", "", "electronics_db");

// 1. FETCH SETTINGS & THRESHOLD
$set = $conn->query("SELECT * FROM settings WHERE id = 1")->fetch_assoc();
$threshold = $set['low_stock_warning_level'] ?? 10;
$shop_name = $set['shop_name'] ?? "Dayananda Electronics";

// 2. AI DETECTION: LOW STOCK ITEMS
$low_stock_list = [];
$detect_query = $conn->query("SELECT gadget_name, stock_count FROM inventory WHERE stock_count <= $threshold");
while($low_item = $detect_query->fetch_assoc()) {
    $low_stock_list[] = $low_item;
}
$alert_count = count($low_stock_list);

// 3. SUMMARY STATS
$total_products = $conn->query("SELECT COUNT(*) as t FROM inventory")->fetch_assoc()['t'];
$total_stock = $conn->query("SELECT SUM(stock_count) as s FROM inventory")->fetch_assoc()['s'];
$total_val = $conn->query("SELECT SUM(stock_count * price) as v FROM inventory")->fetch_assoc()['v'];

// 4. REFILL CALCULATION
$target_level = $threshold * 3;
$refill_res = $conn->query("SELECT SUM($target_level - stock_count) as total_needed FROM inventory WHERE stock_count <= $threshold");
$total_refill_needed = $refill_res->fetch_assoc()['total_needed'] ?? 0;

// 5. CHART DATA
$cat_names = []; $cat_vals = [];
$chart_res = $conn->query("SELECT c.name as category_name, SUM(i.stock_count * i.price) as total_value FROM categories c INNER JOIN inventory i ON c.id = i.category_id GROUP BY c.name HAVING total_value > 0");
while($row = $chart_res->fetch_assoc()){ $cat_names[] = $row['category_name']; $cat_vals[] = $row['total_value']; }

$stock_labels = []; $stock_counts = [];
$stock_res = $conn->query("SELECT gadget_name, stock_count FROM inventory ORDER BY stock_count DESC LIMIT 5");
while($s = $stock_res->fetch_assoc()){ $stock_labels[] = $s['gadget_name']; $stock_counts[] = $s['stock_count']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $shop_name; ?> | Full Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --accent: #c6f021; --sidebar: #000; --bg: #f4f7f6; --text: #8e8e93; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; display: flex; }
        
        /* SIDEBAR STYLES */
        .sidebar { width: 250px; background: var(--sidebar); color: white; height: 100vh; padding: 25px; position: fixed; box-sizing: border-box; overflow-y: auto; }
        .sidebar h2 { color: var(--accent); margin-bottom: 25px; text-transform: uppercase; font-size: 18px; }
        .section-label { color: #555; font-size: 10px; font-weight: bold; text-transform: uppercase; margin: 20px 0 10px 0; }
        .nav-item { padding: 12px 0; color: var(--text); text-decoration: none; display: flex; align-items: center; transition: 0.3s; font-size: 14px; }
        .nav-item i { width: 30px; font-size: 16px; }
        .nav-item:hover, .nav-item.active { color: white; }
        .nav-item.active i { color: var(--accent); }


        .main { margin-left: 250px; flex: 1; padding: 40px; box-sizing: border-box; }
        .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 15px; border: 1px solid #eee; }
        
        .graphics-grid { display: grid; grid-template-columns: 1fr 1.5fr 1fr; gap: 20px; margin-bottom: 30px; }
        .card { background: white; padding: 25px; border-radius: 20px; border: 1px solid #eee; }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: var(--text); font-size: 11px; text-transform: uppercase; padding: 15px; border-bottom: 1px solid #eee; }
        td { padding: 18px 15px; border-bottom: 1px solid #f9f9f9; font-size: 14px; }
        .action-btn { font-size: 18px; margin-right: 15px; text-decoration: none; }
        .refill-badge { color: #ff4d4d; font-size: 11px; font-weight: bold; display: block; margin-top: 5px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2><?php echo $shop_name; ?></h2>
    <div class="section-label">Discover</div>
    <a href="index.php" class="nav-item active"><i class="fa-solid fa-gauge"></i> Dashboard</a>
    
    <div class="section-label">Inventory</div>
    <a href="products.php" class="nav-item"><i class="fa-solid fa-box"></i> Products</a>
    <a href="categories.php" class="nav-item"><i class="fa-solid fa-layer-group"></i> Category</a>
    <a href="suppliers.php" class="nav-item"><i class="fa-solid fa-truck-field"></i> Suppliers</a>
    
    <div class="section-label">Logistics</div>
    <a href="billing.php" class="nav-item"><i class="fa-solid fa-file-invoice-dollar"></i> Billing</a>
    <a href="orders.php" class="nav-item"><i class="fa-solid fa-cart-flatbed"></i> Order</a>
    <a href="delivery.php" class="nav-item"><i class="fa-solid fa-truck-ramp-box"></i> Delivery</a>
    
    <div class="section-label">Settings</div>
    <a href="settings.php" class="nav-item"><i class="fa-solid fa-sliders"></i> Settings</a>
    <a href="login.php?action=logout" class="nav-item" style="color: #ff4d4d;"><i class="fa-solid fa-power-off"></i> Logout</a>
</div>

<div class="main">
    <div class="stats-grid">
        <div class="stat-card"><small>Total Products</small><h2><?php echo $total_products; ?></h2></div>
        <div class="stat-card"><small>Available Stock</small><h2><?php echo number_format($total_stock); ?></h2></div>
        <div class="stat-card"><small>Low Stock</small><h2 style="color:red"><?php echo $alert_count; ?></h2></div>
        <div class="stat-card" style="border-bottom: 3px solid #ff4d4d;"><small>Refill Needed</small><h2 style="color:#ff4d4d"><?php echo $total_refill_needed; ?></h2></div>
        <div class="stat-card"><small>Inv. Value</small><h2>₹<?php echo number_format($total_val, 0); ?></h2></div>
    </div>

    <div class="graphics-grid">
        <div class="card"><h3>Profit Category</h3><canvas id="profitChart"></canvas></div>
        <div class="card"><h3>Stock Analysis</h3><canvas id="stockChart"></canvas></div>
        <div class="card" style="border-top: 5px solid #ff4d4d;">
            <h3 style="color:#ff4d4d">Refill List</h3>
            <div style="max-height: 250px; overflow-y: auto;">
                <?php if($alert_count > 0): foreach($low_stock_list as $item): ?>
                    <div style="padding: 10px 0; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between;">
                        <span style="font-size: 13px;"><?php echo htmlspecialchars($item['gadget_name']); ?></span>
                        <strong style="color:#ff4d4d"><?php echo $item['stock_count']; ?> left</strong>
                    </div>
                <?php endforeach; else: echo "<p>All stock is healthy!</p>"; endif; ?>
            </div>
        </div>
    </div>

    <div class="card">
        <h3>Electronic Inventory List</h3>
        <table>
            <thead><tr><th>PRODUCT DETAILS</th><th>PRICE</th><th>STOCK STATUS</th><th>ACTION</th></tr></thead>
            <tbody>
                <?php
                $res = $conn->query("SELECT i.*, c.name as cname FROM inventory i LEFT JOIN categories c ON i.category_id = c.id ORDER BY i.id DESC");
                while($row = $res->fetch_assoc()) {
                    $curr_stock = $row['stock_count'];
                    echo "<tr>
                        <td><strong>" . htmlspecialchars($row['gadget_name']) . "</strong><br><small style='color:var(--text)'>" . ($row['cname'] ?? 'General') . "</small></td>
                        <td>₹ " . number_format($row['price'], 2) . "</td>
                        <td><strong>$curr_stock Pcs</strong>";
                        if($curr_stock <= $threshold) {
                            $needed = ($threshold * 3) - $curr_stock;
                            echo "<span class='refill-badge'><i class='fa-solid fa-triangle-exclamation'></i> Refill: +$needed</span>";
                        }
                    echo "</td>
                        <td>
                            <a href='edit_product.php?id={$row['id']}' class='action-btn' style='color:#2196f3;'><i class='fa-solid fa-pen-to-square'></i></a>
                            <a href='delete.php?id={$row['id']}' class='action-btn' style='color:#ff4d4d;' onclick='return confirm(\"Delete this item?\")'><i class='fa-solid fa-trash-can'></i></a>
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
new Chart(document.getElementById('profitChart'), {
    type: 'doughnut',
    data: { 
        labels: <?php echo json_encode($cat_names); ?>, 
        datasets: [{ data: <?php echo json_encode($cat_vals); ?>, backgroundColor: ['#2EC4B6','#4caf50','#c9ada7','#6C5CE7','#9C27B0','#B6E2A1'] }] 
    },
    options: { cutout: '70%', plugins: { legend: { position: 'bottom' } } }
});

new Chart(document.getElementById('stockChart'), {
    type: 'bar',
    data: { 
        labels: <?php echo json_encode($stock_labels); ?>, 
        datasets: [{ label: 'Stock', data: <?php echo json_encode($stock_counts); ?>, backgroundColor: '#c6f021' }] 
    }
});
</script>
</body>
</html>