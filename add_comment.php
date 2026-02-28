<?php
session_start();
$conn = new mysqli("localhost", "root", "", "clubconnect");

if (!isset($_SESSION['user_id'])) exit();

$user_id = $_SESSION['user_id'];
$post_id = (int)$_POST['post_id'];
$text = $_POST['comment_text'];

// Reuse your existing filter function here or include it
// (For brevity, assuming $comment_text is filtered and escaped)
$comment_text = $conn->real_escape_string($text);

if (!empty(trim($comment_text))) {
    $sql = "INSERT INTO post_comments (post_id, user_id, comment_text) VALUES ($post_id, $user_id, '$comment_text')";
    if ($conn->query($sql)) {
        // Fetch user info for the immediate UI update
        $user = $conn->query("SELECT fullname, profile_pic FROM users WHERE id = $user_id")->fetch_assoc();
        
        echo json_encode([
            "status" => "success",
            "fullname" => $user['fullname'],
            "profile_pic" => !empty($user['profile_pic']) ? $user['profile_pic'] : 'default-avatar.png',
            "text" => htmlspecialchars($text),
            "time" => "Just now"
        ]);
    }
}
?>