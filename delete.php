<?php
// Enable error reporting so if something is wrong, it tells us instead of showing a white screen
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli("localhost", "root", "", "electronics_db");

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Convert to number to be safe
    $id = intval($id); 
    
    // Perform the delete
    $conn->query("DELETE FROM inventory WHERE id=$id");
}

// Redirect back to dashboard
header("Location: index.php");
exit();
?>