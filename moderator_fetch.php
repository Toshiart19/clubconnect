<?php
session_start();
$conn = new mysqli("localhost","root","","clubconnect");

if(!isset($_SESSION['user_id'])) exit();

$user_id = $_SESSION['user_id'];
$type = $_GET['type'] ?? '';

// --- 1. COUNT LOGIC ---
if($type === "count"){
    $notif = $conn->query("SELECT COUNT(*) as c FROM notifications WHERE user_id = $user_id AND is_read = 0")->fetch_assoc()['c'];
    $msg = $conn->query("SELECT COUNT(*) as c FROM messages WHERE receiver_id = $user_id AND is_read = 0")->fetch_assoc()['c'];

    echo json_encode(["total" => (int)$notif + (int)$msg]);
    exit();
}

// --- 2. NOTIFICATION LIST LOGIC ---
if($type === "notif"){
    // FIX: Only fetch notifications where is_read = 0 so they disappear when clicked
    $res = $conn->query("SELECT id, message, created_at FROM notifications WHERE user_id = $user_id AND is_read = 0 ORDER BY created_at DESC LIMIT 10");

    if($res->num_rows === 0) {
        echo "<div style='padding:15px; color:#94a3b8; text-align:center;'>No new notifications</div>";
    }

    while($row = $res->fetch_assoc()){
        echo "<div class='mod-item' id='notif-".$row['id']."'>";
        echo "<span>" . htmlspecialchars($row['message']) . "</span>";
        echo "<br><small style='color:#64748b;'>".date('M d, g:i A', strtotime($row['created_at']))."</small>";
        // Stylized button to ensure it looks clickable
        echo "<br><button onclick='markNotif(".$row['id'].")' style='background:none; border:none; color:#f87171; cursor:pointer; padding:5px 0; font-size:11px; text-transform:uppercase;'>Mark as Read</button>";
        echo "</div>";
    }
    exit();
}

// --- 3. MESSAGE LIST LOGIC ---
if($type === "msg"){
    // FIX: Only fetch messages where is_read = 0
    $res = $conn->query("SELECT id, sender_name, message, created_at FROM messages WHERE receiver_id = $user_id AND is_read = 0 ORDER BY created_at DESC LIMIT 10");

    if($res->num_rows === 0) {
        echo "<div style='padding:15px; color:#94a3b8; text-align:center;'>Inbox empty</div>";
    }

    while($row = $res->fetch_assoc()){
        echo "<div class='mod-item'>";
        echo "<b>".htmlspecialchars($row['sender_name'])."</b><br>";
        echo htmlspecialchars($row['message']);
        echo "<br><small style='color:#64748b;'>".date('M d, g:i A', strtotime($row['created_at']))."</small>";
        echo "<br><button onclick='markMsg(".$row['id'].")' style='background:none; border:none; color:#f87171; cursor:pointer; padding:5px 0; font-size:11px; text-transform:uppercase;'>Mark as Read</button>";
        echo "</div>";
    }
    exit();
    if ($type === 'msg') {
    // Group messages by student so moderator sees a list of people who messaged
    $res = $conn->query("SELECT m.*, u.fullname FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.receiver_id = $user_id GROUP BY m.sender_id ORDER BY m.created_at DESC");
    
    while($row = $res->fetch_assoc()) {
        echo "<div class='mod-item'>
                <strong>{$row['fullname']}</strong>
                <p style='font-size: 12px; margin: 5px 0;'>{$row['message_text']}</p>
                <button class='btn-mark-read' onclick='openReply(\"{$row['sender_id']}\")'>Reply</button>
              </div>";
    }
}
}

// --- 4. MARK AS READ ACTIONS ---
if($type === "mark_notif"){
    $id = (int)$_GET['id'];
    // Added security check: ensure the notification belongs to the logged-in user
    $conn->query("UPDATE notifications SET is_read = 1 WHERE id = $id AND user_id = $user_id");
    exit();
}

if($type === "mark_msg"){
    $id = (int)$_GET['id'];
    // Added security check: ensure the message belongs to the logged-in user
    $conn->query("UPDATE messages SET is_read = 1 WHERE id = $id AND receiver_id = $user_id");
    exit();
}
?>