<?php
session_start();

// Enable error reporting to diagnose issues
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli("localhost", "root", "", "clubconnect");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure only admins can access
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    exit('Denied: Unauthorized Access');
}

$action = $_GET['action'] ?? '';

/* ===============================
    POST PUBLIC ANNOUNCEMENT
================================ */
if ($action == 'post_announcement') {
    $title = $_POST['title'];
    $message = $_POST['message'];
    $club_id = (int)$_POST['club_id']; // Catch the new field

    // Stackable insert (multiple records can exist)
    $stmt = $conn->prepare("INSERT INTO announcements (title, message, club_id, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("ssi", $title, $message, $club_id);
    
    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?success=broadcasted");
    } else {
        echo "Error: " . $conn->error;
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

    $banner_path = "";
    if (!empty($_FILES['banner_image']['name'])) {
        $target_dir = "assetimages/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_name = time() . "_" . basename($_FILES["banner_image"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["banner_image"]["tmp_name"], $target_file)) {
            $banner_path = $target_file;
        }
    }

    if ($club_id > 0) {
        // UPDATE EXISTING - Changed 'banner_image' to 'Logo'
        $update_sql = "UPDATE clubs SET club_name='$name', description='$desc', hex_color='$color'";
        if (!empty($banner_path)) {
            $update_sql .= ", Logo = '$banner_path'"; 
        }
        $update_sql .= " WHERE id=$club_id"; 
        
        if ($conn->query($update_sql)) {
            header("Location: admin_dashboard.php?status=updated");
        } else {
            die("Update Error: " . $conn->error);
        }
    } else {
        // CREATE NEW - Changed 'banner_image' to 'Logo'
        $final_banner = !empty($banner_path) ? $banner_path : "assetimages/default-banner.jpg";
        $insert_sql = "INSERT INTO clubs (club_name, description, hex_color, Logo) 
                       VALUES ('$name', '$desc', '$color', '$final_banner')";
        
        if ($conn->query($insert_sql)) {
            header("Location: admin_dashboard.php?status=created");
        } else {
            die("Insert Error: " . $conn->error);
        }
    }

/* ===============================
    DELETE CLUB
================================ */
if ($action === 'delete_club') {
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        
        try {
            // FIX: Using 'id' instead of 'club_id' to match your table schema
            $sql = "DELETE FROM clubs WHERE id = $id";
            if ($conn->query($sql)) {
                header("Location: admin_dashboard.php?status=deleted");
                exit();
            } else {
                echo "Error deleting record: " . $conn->error;
            }
        } catch (mysqli_sql_exception $e) {
            die("Error: You cannot delete this club while it has active posts or members.");
        }
    }
}

/* ===============================
    DELETE POST
================================ */
if ($action === 'delete_post') {
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $sql = "DELETE FROM club_posts WHERE id = $id";
        if ($conn->query($sql)) {
            header("Location: admin_dashboard.php?status=post_deleted");
            exit();
        } else {
            die("Delete Error: " . $conn->error);
        }
    }
}
}
?>