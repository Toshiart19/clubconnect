<?php
session_start();
$conn = new mysqli("localhost", "root", "", "clubconnect");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ==============================
   GET USER CLUB MEMBERSHIPS
============================== */
$userClubs = [];
$memberQuery = $conn->query("SELECT club_id FROM club_members WHERE user_id = $user_id");
if ($memberQuery) {
    while ($row = $memberQuery->fetch_assoc()) {
        $userClubs[] = (int)$row['club_id'];
    }
}

/* ==============================
   GET EVENTS (Prev-Next Year)
============================== */
$currentYear = date("Y");
$startDate = ($currentYear - 1) . "-01-01";
$endDate   = ($currentYear + 1) . "-12-31";

$events = [];
$eventQuery = $conn->query("
    SELECT e.*, c.club_name
    FROM events e
    JOIN clubs c ON e.club_id = c.id
    WHERE e.event_date BETWEEN '$startDate' AND '$endDate'
");

if ($eventQuery) {
    while ($row = $eventQuery->fetch_assoc()) {
        $events[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>ClubConnect - Event Calendar</title>

<style>
body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(-45deg, #0f2027, #203a43, #2c5364, #b31217, #e52d27);
    background-size: 400% 400%;
    animation: gradientBG 12s ease infinite;
    color: white;
    padding: 120px 20px 40px;
}

@keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.calendar-container {
    max-width: 1000px;
    margin: auto;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(20px);
    padding: 30px;
    border-radius: 25px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.calendar-header button {
    background: #e52d27;
    border: none;
    padding: 8px 15px;
    border-radius: 10px;
    color: white;
    cursor: pointer;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 10px;
}

.day-name {
    text-align: center;
    font-weight: bold;
    opacity: 0.8;
}

.day {
    height: 100px;
    background: rgba(255,255,255,0.05);
    border-radius: 12px;
    padding: 5px;
    position: relative;
    font-size: 14px;
}

.day-number {
    font-size: 12px;
    opacity: 0.7;
}

.dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 4px;
}

.user-event {
    background: #e52d27;
}

.other-event {
    background: grey;
}

.upcoming {
    margin-top: 30px;
}

.upcoming-item {
    padding: 10px;
    border-left: 4px solid #e52d27;
    background: rgba(255,255,255,0.05);
    margin-bottom: 10px;
    border-radius: 10px;
}
/* ================= TOPBAR ================= */
.topbar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 70px;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(20px);
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 40px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
    z-index: 1000;
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 20px;
}

.topbar-title {
    font-size: 20px;
    font-weight: bold;
}

.topbar a {
    text-decoration: none;
    color: white;
    background: #e52d27;
    padding: 8px 15px;
    border-radius: 10px;
    transition: 0.3s;
}

.topbar a:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}
</style>
</head>
<body>
<div class="topbar">
    <div class="topbar-left">
        <div class="topbar-title">ClubConnect</div>
        <a href="home.php">Home</a>
    </div>
</div>
<div class="calendar-container">

    <div class="calendar-header">
        <h2 id="monthTitle"></h2>
        <div>
            <button onclick="changeMonth(-1)">◀</button>
            <button onclick="changeMonth(1)">▶</button>
        </div>
    </div>

    <div class="calendar-grid" id="calendarGrid"></div>

    <div class="upcoming">
        <h3>Upcoming Events</h3>
        <?php
        $today = date("Y-m-d");
        $upcomingQuery = $conn->query("
            SELECT e.*, c.club_name
            FROM events e
            JOIN clubs c ON e.club_id = c.id
            WHERE e.event_date >= '$today'
            ORDER BY e.event_date ASC
            LIMIT 5
        ");

        if ($upcomingQuery) {
            while ($u = $upcomingQuery->fetch_assoc()) {
                echo "<div class='upcoming-item'>
                        <strong>" . htmlspecialchars($u['title']) . "</strong><br>
                        " . htmlspecialchars($u['club_name']) . " • " .
                        date("M d, Y", strtotime($u['event_date'])) . "
                      </div>";
            }
        }
        ?>
    </div>

</div>

<script>
const events = <?php echo json_encode($events); ?>;
const userClubs = <?php echo json_encode($userClubs); ?>;

let currentDate = new Date();

function renderCalendar() {
    const grid = document.getElementById("calendarGrid");
    grid.innerHTML = "";

    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    document.getElementById("monthTitle").innerText =
        currentDate.toLocaleString('default', { month: 'long' }) + " " + year;

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);

    const days = ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];
    days.forEach(d => {
        const div = document.createElement("div");
        div.className = "day-name";
        div.innerText = d;
        grid.appendChild(div);
    });

    for (let i = 0; i < firstDay.getDay(); i++) {
        grid.appendChild(document.createElement("div"));
    }

    for (let d = 1; d <= lastDay.getDate(); d++) {
        const dateStr = year + "-" +
            String(month + 1).padStart(2, '0') + "-" +
            String(d).padStart(2, '0');

        const dayDiv = document.createElement("div");
        dayDiv.className = "day";

        const num = document.createElement("div");
        num.className = "day-number";
        num.innerText = d;
        dayDiv.appendChild(num);

        events.forEach(ev => {
            if (ev.event_date === dateStr) {
                const dot = document.createElement("span");
                dot.className = "dot " +
                    (userClubs.includes(parseInt(ev.club_id)) ? "user-event" : "other-event");
                dayDiv.appendChild(dot);
            }
        });

        grid.appendChild(dayDiv);
    }
}

function changeMonth(step) {
    currentDate.setMonth(currentDate.getMonth() + step);
    renderCalendar();
}

renderCalendar();
</script>

</body>
</html>