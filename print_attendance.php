<?php
session_start();

/**
 * DATABASE CONNECTION
 * Using the settings from your admin_actions.php
 */
$conn = new mysqli("localhost", "root", "", "clubconnect");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Get the Post ID from the URL
$post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

if ($post_id === 0) {
    die("<div style='padding:20px; color:red;'>Error: No Post ID provided. Please close this window and try again.</div>");
}

// 2. Fetch Post Details (to show the event name at the top)
$post_query = $conn->query("SELECT title FROM club_posts WHERE id = $post_id");
$post_data = $post_query->fetch_assoc();
$event_title = $post_data['title'] ?? 'Club Event';

// 3. Fetch Users who clicked "Join"
// This assumes your join table is 'event_responses' as per your project schema
$sql = "SELECT u.fullname, u.usn 
        FROM users u 
        JOIN event_responses er ON u.id = er.user_id 
        WHERE er.post_id = $post_id AND er.status = 'joining'
        ORDER BY u.fullname ASC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance - <?php echo htmlspecialchars($event_title); ?></title>
    <style>
        /* General Styling */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 40px; color: #333; }
        
        /* Header Section */
        .header { text-align: center; border-bottom: 2px solid #444; margin-bottom: 30px; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; }
        .header p { margin: 5px 0; color: #666; }

        /* Meta Info (Date/Time) */
        .meta-info { display: flex; justify-content: space-between; margin-bottom: 20px; font-weight: bold; }

        /* Table Styling */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        table td {
    height: 30px; /* Gives a consistent height for handwriting */
    vertical-align: middle;
}
        .col-num { width: 30px; text-align: center; }
        .col-sig { width: 150px; }
        .col-time { width: 100px; }

        /* Signature Section */
        .footer-sig { margin-top: 50px; display: flex; justify-content: space-between; }
        .sig-line { border-top: 1px solid #000; width: 150px; text-align: center; padding-top: 5px; margin-top: 40px; }

        /* Print Controls */
        .no-print-bar { 
            background: #333; color: white; padding: 10px; 
            position: fixed; top: 0; left: 0; right: 0; 
            display: flex; justify-content: space-between; align-items: center;
        }
        .btn-print { background: #28a745; color: white; border: none; padding: 8px 15px; cursor: pointer; border-radius: 4px; }

        @media print {
            .no-print-bar { display: none !important; }
            body { padding: 0; margin: 0; }
            button { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <span>Attendance Preview</span>
        <button class="btn-print" onclick="window.print()">Print Now</button>
    </div>

    <div class="header">
        <h1>Club Connect</h1>
        <p>Official Event Attendance Sheet</p>
    </div>

    <div class="meta-info">
        <div>Event: <?php echo htmlspecialchars($event_title); ?></div>
        <div>Date: ____________________</div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th>Student Name</th>
                <th>Student ID / USN</th>
                <th class="col-time">Time In</th>
                <th class="col-time">Time Out</th>
                <th class="col-sig">Signature</th>
            </tr>
        </thead>
        <tbody>
       <?php 
    if ($result->num_rows > 0):
        $count = 1;
        while($row = $result->fetch_assoc()): 
    ?>
        <tr>
            <td class="col-num"><?php echo $count++; ?></td>
            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
            <td><?php echo htmlspecialchars($row['usn']); ?></td>
            <td></td> <td></td> <td class="col-sig"></td> </tr>
    <?php 
        endwhile; 
    else:
    ?>
        <tr>
            <td colspan="6" style="text-align:center; padding: 30px;">No students have joined this event yet.</td>
        </tr>
    <?php endif; ?>
        </tbody>
    </table>

    <div class="footer-sig">
        <div class="sig-line">Club Moderator Signature</div>
        <div class="sig-line">Date Signed</div>
    </div>

    <script>
        // Trigger print dialog as soon as the popup finishes loading
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500); // Small delay to ensure styles are loaded
        };
    </script>
</body>
</html>