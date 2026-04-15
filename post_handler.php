<?php
session_start();
$conn = new mysqli("localhost", "root", "", "clubconnect");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_post'])) {
    $club_id = (int)$_POST['club_id'];
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    $location_address = $conn->real_escape_string($_POST['location_address']);
    
    // --- FIX: Capture event_date ---
    // If empty, set to NULL (without quotes). If filled, escape and wrap in quotes.
    $event_date = !empty($_POST['event_date']) ? "'" . $conn->real_escape_string($_POST['event_date']) . "'" : "NULL";
    
    $image_path = "";

    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] == 0) {
        $target_dir = "assetimages/posts/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $file_name = time() . "_" . basename($_FILES["post_image"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["post_image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    // --- UPDATED SQL: Added event_date column ---
    // Note: $event_date is NOT wrapped in quotes here because the variable already contains them if needed.
    $sql = "INSERT INTO club_posts (club_id, title, content, location_address, event_date, image_url) 
            VALUES ('$club_id', '$title', '$content', '$location_address', $event_date, '$image_path')";

    if ($conn->query($sql)) {
        header("Location: club_home.php?id=" . $club_id);
    } else {
        echo "Error: " . $conn->error;
    }
}
?>