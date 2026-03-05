<?php
session_start();
$conn = new mysqli("localhost", "root", "", "clubconnect");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit('Unauthorized Access');
}

$action = $_GET['action'] ?? '';

// ACTION: DELETE POST
if ($action === 'delete_post') {
    $id = (int)$_GET['id'];
    // Corrected table name here
    $stmt = $conn->prepare("DELETE FROM club_posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: admin_dashboard.php?msg=PostDeleted");
    exit();
}

// ACTION: ADD USER TO CLUB
if ($action === 'assign_user') {
    $u_id = (int)$_POST['user_id'];
    $c_id = (int)$_POST['club_id'];
    
    // Check if membership table exists and join them
    // Ensure you have a 'club_members' table
    $stmt = $conn->prepare("INSERT IGNORE INTO club_members (user_id, club_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $u_id, $c_id);
    $stmt->execute();
    
    header("Location: admin_dashboard.php?msg=UserAssigned");
    exit();
}
?>