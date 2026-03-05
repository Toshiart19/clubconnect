<?php
session_start();
$conn = new mysqli("localhost", "root", "", "clubconnect");

// Security Check: Only admins allowed
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: home.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];

// Fetch Stats using corrected table name
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_clubs = $conn->query("SELECT COUNT(*) as count FROM clubs")->fetch_assoc()['count'];
$total_posts_res = $conn->query("SELECT COUNT(*) as count FROM club_posts");
$total_posts = ($total_posts_res) ? $total_posts_res->fetch_assoc()['count'] : 0;

// Fetch Clubs for Management
$clubs_res = $conn->query("SELECT * FROM clubs");

// Fetch Posts with Club Info and hex_color for the UI differentiation
$posts_res = $conn->query("
    SELECT p.*, c.club_name, c.hex_color 
    FROM club_posts p 
    JOIN clubs c ON p.club_id = c.id 
    ORDER BY p.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Control - ClubConnect</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <style>
        /* Reusing your home.php Root & Body Variables */
        :root {
            --bg-gradient: linear-gradient(-45deg, #0f2027, #203a43, #2c5364, #b31217, #e52d27);
            --card-bg: rgba(0, 0, 0, 0.7);
            --text-color: #fff;
            --accent: #b31217;
        }

        body {
            background: var(--bg-gradient);
            background-size: 400% 400%;
            animation: gradientMove 12s ease infinite;
            color: var(--text-color);
            font-family: 'Segoe UI', sans-serif;
            margin: 0; padding: 20px;
        }

        @keyframes gradientMove { 0%{background-position:0% 50%;} 50%{background-position:100% 50%;} 100%{background-position:0% 50%;} }

        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 80px;
        }

        .admin-card {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 10px;
        }

        /* Club Management Table */
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { text-align: left; opacity: 0.6; padding: 10px; }
        td { padding: 12px 10px; border-bottom: 1px solid rgba(255,255,255,0.05); }

        /* Color-Coded Posts */
        .post-feed { display: flex; flex-direction: column; gap: 15px; max-height: 500px; overflow-y: auto; }
        .post-item {
            padding: 15px;
            border-radius: 12px;
            position: relative;
            color: white;
        }

        .delete-btn {
            background: #ff4d4d;
            border: none;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
        }

        #calendar { background: rgba(255,255,255,0.05); padding: 15px; border-radius: 15px; }
        
        /* Modal for User/Club Creation */
        .modal { display: none;
            position: fixed;
            z-index: 2000;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(5px);
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>

<div class="topbar" style="display:flex; justify-content: space-between; position:fixed; top:0; left:0; width:100%; padding: 15px 40px; background: rgba(0,0,0,0.4); backdrop-filter:blur(10px); z-index:1000;">
    <div style="display:flex; align-items:center; gap:10px;">
        <img src="/clubconnect/assetimages/cc.png" height="30">
        <span style="font-weight:bold;">ADMIN PANEL</span>
    </div>
    <div style="display:flex; gap:20px; align-items:center;">
        <span>Welcome, Admin <?php echo $fullname; ?></span>
        <a href="home.php" style="color:white; text-decoration:none; font-size:14px; background:rgba(255,255,255,0.1); padding:5px 15px; border-radius:20px;">View Site</a>
        <a href="logout.php" style="color:#ff4d4d; text-decoration:none; font-size:14px;">Logout</a>
    </div>
</div>

<div class="admin-grid">
    
    <div class="admin-card">
        <div class="section-header">
            <h3><i data-lucide="shield"></i> Manage Clubs</h3>
            <button onclick="openClubModal()" class="delete-btn" style="background:var(--accent)">+ New Club</button>
        </div>
        <table>
            <thead>
                <tr><th>Club Name</th><th>Color</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php while($club = $clubs_res->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo $club['club_name']; ?></strong></td>
                    <td><div style="width:20px; height:20px; border-radius:40%; background:<?php echo $club['hex_color']; ?>"></div></td>
                    <td>
                        <button onclick="editClub(<?php echo $club['id']; ?>)" style="background:none; border:none; color:#aaa; cursor:pointer;"><i data-lucide="edit-3" size="16"></i></button>
                        <button onclick="deleteClub(<?php echo $club['id']; ?>)" style="background:none; border:none; color:#ff4d4d; cursor:pointer;"><i data-lucide="trash-2" size="16"></i></button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-card" style="grid-column: span 1;">
        <div class="section-header">
            <h3><i data-lucide="calendar"></i> Master Activities</h3>
        </div>
        <div id="calendar"></div>
    </div>

    <div class="admin-card">
        <div class="section-header">
            <h3><i data-lucide="layout"></i> Recent Posts</h3>
        </div>
        <div class="post-feed">
            <?php while($post = $posts_res->fetch_assoc()): ?>
            <div class="post-item" style="background: <?php echo $post['hex_color']; ?>dd; border: 1px solid <?php echo $post['hex_color']; ?>;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div>
                        <small style="text-transform:uppercase; font-weight:bold;"><?php echo $post['club_name']; ?></small>
                        <p style="margin:5px 0;"><?php echo htmlspecialchars($post['content']); ?></p>
                    </div>
                    <button class="delete-btn" onclick="deletePost(<?php echo $post['id']; ?>)">Delete</button>
                </div>
                <small style="opacity:0.8;"><?php echo date('M d, h:i A', strtotime($post['created_at'])); ?></small>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="admin-card">
        <div class="section-header">
            <h3><i data-lucide="users"></i> Club Memberships</h3>
        </div>
        <form action="admin_actions.php?action=assign_user" method="POST" style="display:flex; flex-direction:column; gap:10px;">
            <select name="user_id" required style="padding:10px; border-radius:8px; background:var(--input-bg); color:white;">
                <option value="">Select Student...</option>
                <?php
                $users = $conn->query("SELECT id, fullname FROM users WHERE role='student'");
                while($u = $users->fetch_assoc()) echo "<option value='{$u['id']}'>{$u['fullname']}</option>";
                ?>
            </select>
            <select name="club_id" required style="padding:10px; border-radius:8px; background:var(--input-bg); color:white;">
                <option value="">Select Club...</option>
                <?php
                $clubs_res->data_seek(0);
                while($c = $clubs_res->fetch_assoc()) echo "<option value='{$c['id']}'>{$c['club_name']}</option>";
                ?>
            </select>
            <button type="submit" style="padding:12px; background:var(--accent); color:white; border:none; border-radius:8px; cursor:pointer;">Add User to Club</button>
        </form>
    </div>

</div>

<script>
    lucide.createIcons();

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: { left: 'prev,next', center: 'title', right: '' },
            events: 'fetch_admin_events.php', // This file will return all events from all clubs
            eventDidMount: function(info) {
                // Info contains the hex_color from the database
                if (info.event.extendedProps.color) {
                    info.el.style.backgroundColor = info.event.extendedProps.color;
                    info.el.style.borderColor = info.event.extendedProps.color;
                }
            }
        });
        calendar.render();
    });

    function deletePost(postId) {
        if(confirm('Delete this post permanently?')) {
            window.location.href = `admin_actions.php?action=delete_post&id=${postId}`;
        }
    }

    function deleteClub(clubId) {
        if(confirm('Warning: This will delete all club data and posts!')) {
            window.location.href = `admin_actions.php?action=delete_club&id=${clubId}`;
        }
    }
</script>
</body>
</html>