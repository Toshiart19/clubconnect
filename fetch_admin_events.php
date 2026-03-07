<?php
$conn = new mysqli("localhost", "root", "", "clubconnect");

// Added p.content and p.id to the query
$result = $conn->query("
    SELECT 
        e.title, 
        e.event_date as start, 
        c.hex_color as color, 
        c.club_name,
        e.description 
    FROM club_events e 
    JOIN clubs c ON e.club_id = c.id
");

$events = [];
while($row = $result->fetch_assoc()) {
    $events[] = [
        'title' => $row['club_name'] . ": " . $row['title'],
        'start' => $row['start'],
        'backgroundColor' => $row['color'],
        'borderColor' => $row['color'],
        'extendedProps' => [
            'description' => $row['description'],
            'club' => $row['club_name']
        ],
        'allDay' => true
    ];
}
header('Content-Type: application/json');
echo json_encode($events);
?>