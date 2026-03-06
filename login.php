<?php
session_start();

// --- 1. LOGOUT LOGIC ---
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_unset();
    session_destroy();
    header("Location: login.php?msg=logged_out");
    exit();
}

// --- 2. DATABASE CONNECTION WITH ERROR GUARD ---
// This prevents the "White Page"
try {
    $conn = mysqli_connect("localhost", "root", "", "electronics_db");
    if (!$conn) { throw new Exception("Connection failed"); }
} catch (Exception $e) {
    die("<div style='background:#1e293b; color:white; height:100vh; display:flex; flex-direction:column; justify-content:center; align-items:center; font-family:sans-serif;'>
            <h1 style='color:#00d2ff;'>Connection Error</h1>
            <p>Please open <b>XAMPP Control Panel</b> and click <b>Start</b> next to MySQL.</p>
            <button onclick='location.reload()' style='padding:10px 20px; background:#00d2ff; border:none; border-radius:5px; cursor:pointer;'>Retry Connection</button>
         </div>");
}

// Fetch Shop Settings
$settings_res = mysqli_query($conn, "SELECT * FROM settings WHERE id=1");
$set = mysqli_fetch_assoc($settings_res);

$display_name = isset($set['shop_name']) ? $set['shop_name'] : "VENTORIE";
$db_user = isset($set['owner_username']) ? $set['owner_username'] : "admin";
$db_pass = isset($set['owner_password']) ? $set['owner_password'] : "1234";

// --- 3. LOGIN LOGIC ---
if(isset($_POST['login_btn'])) {
    $user_input = mysqli_real_escape_string($conn, $_POST['username']);
    $pass_input = mysqli_real_escape_string($conn, $_POST['password']);

    if($user_input === $db_user && $pass_input === $db_pass) {
        $_SESSION['owner'] = $user_input;
        header("Location: index.php");
        exit();
    } else {
        echo "<script>alert('Invalid Credentials!');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $display_name; ?> | Login</title>
    <style>
        body { background: #0f172a; color: white; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: #1e293b; padding: 40px; border-radius: 15px; border: 1px solid #00d2ff; text-align: center; box-shadow: 0 10px 30px rgba(0, 210, 255, 0.2); }
        h2 { margin-bottom: 25px; text-transform: uppercase; letter-spacing: 1px; color: #fff; }
        input { display: block; width: 280px; padding: 12px; margin: 15px auto; border-radius: 8px; border: none; font-size: 14px; background: #f8fafc; color: #0f172a; outline: none; }
        button { background: #00d2ff; border: none; padding: 12px; border-radius: 8px; font-weight: bold; cursor: pointer; width: 100%; color: #0f172a; font-size: 16px; transition: 0.3s; }
        button:hover { background: #00b4db; transform: scale(1.02); }
        .msg { color: #c6f021; font-size: 13px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="box">
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'logged_out') echo "<p class='msg'>Logged out successfully!</p>"; ?>
        <h2><?php echo $display_name; ?> LOGIN</h2>
        
        <form method="POST" action="login.php">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login_btn">LOGIN</button>
        </form>
    </div>
</body>
</html>