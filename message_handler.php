<?php
session_start();
$conn = new mysqli("localhost", "root", "", "clubconnect");
$user_id = $_SESSION['user_id'];

$action = $_GET['action'] ?? '';

if ($action === 'send') {
    $club_id = (int)$_POST['club_id'];
    $msg = $conn->real_escape_string($_POST['message']);
    
    // Find the moderator for this club
    $mod = $conn->query("SELECT id FROM users WHERE managed_club_id = $club_id AND role = 'moderator' LIMIT 1")->fetch_assoc();
    $receiver_id = $mod['id'];

    $conn->query("INSERT INTO messages (sender_id, receiver_id, club_id, message_text) VALUES ($user_id, $receiver_id, $club_id, '$msg')");
}

if ($action === 'fetch') {
    $club_id = (int)$_GET['club_id'];
    // Get messages between this user and the club moderator
    $res = $conn->query("SELECT * FROM messages WHERE club_id = $club_id AND (sender_id = $user_id OR receiver_id = $user_id) ORDER BY created_at ASC");
    
    while($m = $res->fetch_assoc()) {
        $is_mine = ($m['sender_id'] == $user_id);
        $align = $is_mine ? 'align-self: flex-end; background: #2563eb;' : 'align-self: flex-start; background: #334155;';
        echo "<div style='max-width: 80%; padding: 8px 12px; border-radius: 12px; color: white; font-size: 14px; $align'>
                {$m['message_text']}
              </div>";
    }
}
?>