<?php
session_start();
$conn = new mysqli("localhost", "root", "", "clubconnect");

$events = [];
// We fetch events for ALL clubs since this is the Admin view
$query = "SELECT p.id, p.title, p.content, p.event_date, c.club_name, c.hex_color 
          FROM club_posts p 
          JOIN clubs c ON p.club_id = c.id 
          WHERE p.event_date IS NOT NULL";

$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $events[] = [
        'id'    => $row['id'],
        'title' => $row['title'] ?: "Club Update",
        'start' => $row['event_date'],
        'extendedProps' => [
            'description' => $row['content'],
            'club'        => $row['club_name'],
            'color'       => $row['hex_color'],
            'post_id'     => $row['id']
        ]
    ];
}

header('Content-Type: application/json');
echo json_encode($events);