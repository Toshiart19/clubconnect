<?php
session_start();
$conn = new mysqli("localhost","root","","clubconnect");

if(!isset($_SESSION['user_id'])) exit();

$mod_id = $_SESSION['user_id'];
$student_id = (int)$_GET['student_id'];

$res = $conn->query("
SELECT m.*, u.fullname, u.profile_picture
FROM messages m
JOIN users u ON m.sender_id = u.id
WHERE 
(m.sender_id = $student_id AND m.receiver_id = $mod_id)
OR
(m.sender_id = $mod_id AND m.receiver_id = $student_id)
ORDER BY m.created_at ASC
");

while($row = $res->fetch_assoc()){

$class = ($row['sender_id'] == $mod_id) ? "mod-msg" : "student-msg";

$pic = !empty($row['profile_picture']) ? $row['profile_picture'] : "default.png";

echo "<div class='chat-row $class'>";

echo "<img src='$pic' class='chat-avatar'>";

echo "<div class='chat-bubble'>";
echo htmlspecialchars($row['message_text']);
echo "<br><small>".date('M d g:i A', strtotime($row['created_at']))."</small>";
echo "</div>";

echo "</div>";
}
?>