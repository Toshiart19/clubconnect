<?php
session_start();
$conn = new mysqli("localhost", "root", "", "clubconnect");

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: home.php");
    exit();
}

$fullname = $_SESSION['fullname'];

// Fetch Stats
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_clubs = $conn->query("SELECT COUNT(*) as count FROM clubs")->fetch_assoc()['count'];
$total_posts = $conn->query("SELECT COUNT(*) as count FROM club_posts")->fetch_assoc()['count'];

$clubs_res = $conn->query("SELECT * FROM clubs");
$posts_res = $conn->query("SELECT p.*, c.club_name, c.hex_color FROM club_posts p JOIN clubs c ON p.club_id = c.id ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - ClubConnect</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <style>
        :root {
            --bg-gradient: linear-gradient(-45deg, #0f2027, #203a43, #2c5364, #b31217, #e52d27);
            --card-bg: rgba(0, 0, 0, 0.8);
            --text-color: #fff;
            --accent: #b31217;
            --input-bg: rgba(255,255,255,0.1);
        }

        body {
            background: var(--bg-gradient); background-size: 400% 400%; animation: gradientMove 12s ease infinite;
            color: var(--text-color); font-family: 'Segoe UI', sans-serif; margin: 0; padding: 20px;
        }
        @keyframes gradientMove { 0%{background-position:0% 50%;} 50%{background-position:100% 50%;} 100%{background-position:0% 50%;} }

        .admin-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; margin-top: 80px; }
        .admin-card { background: var(--card-bg); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 25px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; }

        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 12px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.05); }

        .modal { 
            display: none; position: fixed; z-index: 3000; left: 0; top: 0; width: 100%; height: 100%; 
            background: rgba(0,0,0,0.8); backdrop-filter: blur(8px); justify-content: center; align-items: center; 
        }
        .modal-content { 
            background: #1a1a1a; width: 450px; padding: 30px; border-radius: 20px; border: 1px solid var(--accent);
        }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 14px; opacity: 0.8; }
        .form-group input, .form-group textarea { 
            width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); 
            background: var(--input-bg); color: white; 
        }
        .btn-save { width: 100%; padding: 12px; background: var(--accent); border: none; color: white; border-radius: 8px; cursor: pointer; font-weight: bold; }

        #calendar { background: rgba(0,0,0,0.3); border-radius: 15px; padding: 10px; color: white; }
        .fc-theme-standard td, .fc-theme-standard th { border: 1px solid rgba(255,255,255,0.1); }
        .fc-event {
    cursor: pointer;
    padding: 2px 5px;
    font-size: 13px;
    border-radius: 4px;
}

/* Bigger Delete Buttons */
.delete-btn, .btn-delete-large {
    background: #ff4d4d;
    border: none;
    color: white;
    padding: 10px 18px; /* Increased padding */
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px; /* Bigger font */
    font-weight: bold;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: 0.3s;
}

.delete-btn:hover {
    background: #ff0000;
    transform: scale(1.05);
}

/* Event Popup Styling */
#eventDetailModal .modal-content {
    border-top: 10px solid var(--accent);
}
    </style>
</head>
<body>

<div class="topbar" style="display:flex; justify-content: space-between; position:fixed; top:0; left:0; width:100%; padding: 15px 40px; background: rgba(0,0,0,0.4); backdrop-filter:blur(10px); z-index:1000;">
    <div style="display:flex; align-items:center; gap:10px;">
        <img src="/clubconnect/assetimages/cc.png" height="30">
        <span style="font-weight:bold;">ADMIN DASHBOARD</span>
    </div>
    <div style="display:flex; gap:20px; align-items:center;">
        <a href="home.php" style="color:white; text-decoration:none;">View Site</a>
        <a href="logout.php" style="color:#ff4d4d; text-decoration:none;">Logout</a>
    </div>
</div>

<div class="admin-grid">
    <div class="admin-card">
        <div class="section-header">
            <h3>Manage Clubs</h3>
            <button onclick="openClubModal()" style="background:var(--accent); color:white; border:none; padding:8px 15px; border-radius:8px; cursor:pointer;">+ Add Club</button>
        </div>
        <table>
            <?php while($club = $clubs_res->fetch_assoc()): ?>
            <tr>
                <td><div style="width:12px; height:12px; border-radius:50%; background:<?php echo $club['hex_color']; ?>"></div></td>
                <td><?php echo $club['club_name']; ?></td>
                <td>
                    <button onclick='editClub(<?php echo json_encode($club); ?>)' style="background:none; border:none; color:cyan; cursor:pointer;"><i data-lucide="edit"></i></button>
                    <button onclick="deleteClub(<?php echo $club['id']; ?>)" style="background:none; border:none; color:red; cursor:pointer;"><i data-lucide="trash"></i></button>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="admin-card">
        <div class="section-header"><h3>Club Events</h3></div>
        <div id="calendar"></div>
    </div>

    <div class="admin-card">
        <div class="section-header"><h3>Recent Posts</h3></div>
        <div style="max-height: 400px; overflow-y: auto;">
            <?php while($post = $posts_res->fetch_assoc()): ?>
            <div style="background:<?php echo $post['hex_color']; ?>22; border-left:4px solid <?php echo $post['hex_color']; ?>; padding:10px; margin-bottom:10px; border-radius:8px;">
                <div style="display:flex; justify-content:space-between;">
                    <strong><?php echo $post['club_name']; ?></strong>
                    <button onclick="deletePost(<?php echo $post['id']; ?>)" style="color:red; background:none; border:none; cursor:pointer;">&times;</button>
                </div>
                <p style="font-size:16px;"><?php echo $post['content']; ?></p>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<div id="clubModal" class="modal">
    <div class="modal-content">
        <div class="section-header">
            <h3 id="modalTitle">Add New Club</h3>
            <span onclick="closeModal()" style="cursor:pointer;">&times;</span>
        </div>
        <form action="admin_actions.php?action=save_club" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="club_id" id="club_id">
            <div class="form-group">
                <label>Club Name</label>
                <input type="text" name="club_name" id="club_name" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="description" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Theme Color</label>
                <input type="color" name="hex_color" id="hex_color" style="height:40px;">
            </div>
            <div class="form-group">
                <label>Club Banner (Leave blank to keep current)</label>
                <input type="file" name="banner_image" accept="image/*">
            </div>
            <button type="submit" class="btn-save">Save Club Details</button>
        </form>
    </div>
</div>

<script>
    lucide.createIcons();

    // Calendar Initialization
    document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'day_grid_month', // Ensure correct view name
        events: 'fetch_admin_events.php',
        height: 550,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },
        // INTERACTABLE FUNCTION
        eventClick: function(info) {
            const clubName = info.event.extendedProps.club;
            const description = info.event.extendedProps.description || "No details provided.";
            
            // Create a simple alert or use a custom modal
            alert("Club: " + clubName + "\n\nEvent: " + info.event.title + "\n\nDetails: " + description);
            
            // Optional: If you want a fancy modal, call a function here
            // showEventDetails(info.event);
        }
    });
    calendar.render();
});

    function openClubModal() {
        document.getElementById('modalTitle').innerText = "Add New Club";
        document.getElementById('club_id').value = "";
        document.getElementById('club_name').value = "";
        document.getElementById('description').value = "";
        document.getElementById('hex_color').value = "#b31217";
        document.getElementById('clubModal').style.display = "flex";
    }

    function editClub(club) {
        document.getElementById('modalTitle').innerText = "Edit Club";
        document.getElementById('club_id').value = club.id;
        document.getElementById('club_name').value = club.club_name;
        document.getElementById('description').value = club.description;
        document.getElementById('hex_color').value = club.hex_color;
        document.getElementById('clubModal').style.display = "flex";
    }

    function closeModal() { document.getElementById('clubModal').style.display = "none"; }

    function deletePost(id) { if(confirm('Delete post?')) window.location.href='admin_actions.php?action=delete_post&id='+id; }
    function deleteClub(id) { if(confirm('Delete club and ALL its data?')) window.location.href='admin_actions.php?action=delete_club&id='+id; }
</script>
</body>
</html>