<?php
session_start();
$conn = new mysqli("localhost", "root", "", "clubconnect");

if ($_SESSION['role'] !== 'admin') exit('Denied');

$action = $_GET['action'] ?? '';

if ($action === 'save_club') {
    $club_id = $_POST['club_id'];
    $name = $_POST['club_name'];
    $desc = $_POST['description'];
    $color = $_POST['hex_color'];

    // Handle Image Upload
    $banner_sql = "";
    if (!empty($_FILES['banner_image']['name'])) {
        $target_dir = "assetimages/";
        $file_name = time() . "_" . basename($_FILES["banner_image"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["banner_image"]["tmp_name"], $target_file)) {
            $banner_sql = ", banner_image = '$target_file'";
        }
    }

    if (!empty($club_id)) {
        // UPDATE EXISTING
        $conn->query("UPDATE clubs SET club_name='$name', description='$desc', hex_color='$color' $banner_sql WHERE id=$club_id");
    } else {
        // CREATE NEW
        $banner_val = !empty($file_name) ? "assetimages/$file_name" : "assetimages/default-banner.jpg";
        $conn->query("INSERT INTO clubs (club_name, description, hex_color, banner_image) VALUES ('$name', '$desc', '$color', '$banner_val')");
    }
    header("Location: admin_dashboard.php");
}

if ($action === 'delete_post') {
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM club_posts WHERE id = $id");
    header("Location: admin_dashboard.php");
}
?>