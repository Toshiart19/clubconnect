<?php
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "clubconnect");

// if the user isn't logged in, safely return zeros to prevent JS crashes
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['unreadNotifs' => 0, 'unreadMsgs' => 0, 'notifications' => [], 'messages' => []]);
    exit();
}

$user_id = $_SESSION['user_id'];

// get accurate unread counts
$notif_result = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE user_id = '$user_id' AND is_read = 0");
$notif_count = $notif_result ? (int)$notif_result->fetch_assoc()['total'] : 0;

$msg_result = $conn->query("SELECT COUNT(*) as total FROM messages WHERE receiver_id = '$user_id' AND is_read = 0");
$msg_count = $msg_result ? (int)$msg_result->fetch_assoc()['total'] : 0;

// fetch the 5 most recent notifications
$notifications = [];
$notif_query = $conn->query("SELECT message, created_at, is_read FROM notifications WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT 5");
if ($notif_query) {
    while($row = $notif_query->fetch_assoc()) {
        $notifications[] = $row;
    }
}

// fetch the 5 most recent messages (with the JOIN to get the sender's name!)
$messages = [];
$msg_query = $conn->query("
    SELECT u.fullname AS sender_name, m.message_text AS message, m.created_at, m.is_read 
    FROM messages m 
    JOIN users u ON m.sender_id = u.id 
    WHERE m.receiver_id = '$user_id' 
    ORDER BY m.created_at DESC 
    LIMIT 5
");
if ($msg_query) {
    while($row = $msg_query->fetch_assoc()) {
        $messages[] = $row;
    }
}

// send the perfectly formatted JSON package back to home.php
echo json_encode([
    'unreadNotifs' => $notif_count,
    'unreadMsgs' => $msg_count,
    'notifications' => $notifications,
    'messages' => $messages
]);
$conn->close();
