<?php
session_start();
$conn = new mysqli("localhost", "root", "", "clubconnect");

$user_id = $_SESSION['user_id'];

// SQL to get events for clubs the student has joined
$query = "SELECT events.*, clubs.club_name 
          FROM events 
          JOIN club_members ON events.club_id = club_members.club_id 
          JOIN clubs ON events.club_id = clubs.id
          WHERE club_members.user_id = '$user_id' 
          ORDER BY events.event_date ASC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Events - ClubConnect</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 20px; }
        .event-card { background: white; padding: 20px; border-radius: 12px; margin-bottom: 15px; border-left: 5px solid #b31217; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .event-date { font-weight: bold; color: #b31217; }
        .club-tag { font-size: 12px; background: #eee; padding: 3px 8px; border-radius: 10px; }
    </style>
</head>
<body>

<h1>📅 Your Club Schedule</h1>
<a href="home.php">Back to Dashboard</a><br><br>

<?php if ($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
        <div class="event-card">
            <span class="club-tag"><?php echo $row['club_name']; ?></span>
            <h3><?php echo $row['title']; ?></h3>
            <p class="event-date">🗓 <?php echo date('F j, Y - g:i A', strtotime($row['event_date'])); ?></p>
            <p><?php echo $row['description']; ?></p>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No upcoming events for your clubs. Join more clubs to see events!</p>
<?php endif; ?>

</body>
</html>