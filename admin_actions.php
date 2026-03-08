<?php
session_start();

// Enable error reporting to diagnose "white screen" issues
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli("localhost", "root", "", "clubconnect");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure only admins can access this script
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    exit('Denied: Unauthorized Access');
}

$action = $_GET['action'] ?? '';

/* ===============================
    POST PUBLIC ANNOUNCEMENT
================================ */
if ($action === 'post_announcement') {
    $title = $conn->real_escape_string($_POST['title']);
    $message = $conn->real_escape_string($_POST['message']);
    $admin_id = $_SESSION['user_id'];

    $sql = "INSERT INTO announcements (admin_id, title, message) VALUES ('$admin_id', '$title', '$message')";

    if ($conn->query($sql)) {
        header("Location: admin_dashboard.php?status=announced");
        exit();
    } else {
        die("Error posting announcement: " . $conn->error);
    }
}

/* ===============================
    SAVE CLUB (CREATE/UPDATE)
================================ */
if ($action === 'save_club') {
    $club_id = isset($_POST['club_id']) ? (int)$_POST['club_id'] : 0;
    $name = $conn->real_escape_string($_POST['club_name']);
    $desc = $conn->real_escape_string($_POST['description']);
    $color = $conn->real_escape_string($_POST['hex_color']);

    // Handle Image Upload
    $banner_path = "";
    if (!empty($_FILES['banner_image']['name'])) {
        $target_dir = "assetimages/";
        $file_name = time() . "_" . basename($_FILES["banner_image"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["banner_image"]["tmp_name"], $target_file)) {
            $banner_path = $target_file;
        }
    }

    if ($club_id > 0) {
        // UPDATE EXISTING
        $update_sql = "UPDATE clubs SET club_name='$name', description='$desc', hex_color='$color'";
        if (!empty($banner_path)) {
            $update_sql .= ", banner_image = '$banner_path'";
        }
        $update_sql .= " WHERE club_id=$club_id"; // Using club_id based on your schema
        
        if ($conn->query($update_sql)) {
            header("Location: admin_dashboard.php?status=updated");
        } else {
            die("Update Error: " . $conn->error);
        }
    } else {
        // CREATE NEW
        $final_banner = !empty($banner_path) ? $banner_path : "assetimages/default-banner.jpg";
        
        // Note: I added 'banner_image' and 'status' set to 'active'
        $insert_sql = "INSERT INTO clubs (club_name, description, hex_color, banner_image, status) 
                       VALUES ('$name', '$desc', '$color', '$final_banner', 'active')";
        
        if ($conn->query($insert_sql)) {
            header("Location: admin_dashboard.php?status=created");
        } else {
            die("Insert Error: " . $conn->error);
        }
    }
    exit();
}

/* ===============================
    DELETE CLUB
================================ */
if ($action === 'delete_club') {
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        
        // Use a try-catch to prevent white screen on Foreign Key errors
        try {
            $sql = "DELETE FROM clubs WHERE club_id = $id";
            if ($conn->query($sql)) {
                header("Location: admin_dashboard.php?status=deleted");
                exit();
            } else {
                echo "Error deleting record: " . $conn->error;
            }
        } catch (mysqli_sql_exception $e) {
            die("Stop! You cannot delete this club because it has members or posts linked to it. Remove those first.");
        }
    }
}

/* ===============================
    DELETE POST
================================ */
if ($action === 'delete_post') {
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM club_posts WHERE id = $id");
    header("Location: admin_dashboard.php?status=post_deleted");
    exit();
}
?>