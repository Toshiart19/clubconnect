<?php
$conn = new mysqli("localhost", "root", "", "clubconnect");

$result = $conn->query("
    SELECT e.title, e.start_date as start, c.hex_color as color 
    FROM events e 
    JOIN clubs c ON e.club_id = c.id
");

$events = [];
while($row = $result->fetch_assoc()) {
    $events[] = [
        'title' => $row['title'],
        'start' => $row['start'],
        'backgroundColor' => $row['color'],
        'borderColor' => $row['color']
    ];
}
echo json_encode($events);
?>