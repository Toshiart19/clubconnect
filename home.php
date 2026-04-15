<?php
session_start();
$conn = new mysqli("localhost", "root", "", "clubconnect");
// Fetch the latest global announcement
$ann_res = $conn->query("SELECT * FROM announcements 
                         WHERE created_at >= NOW() - INTERVAL 1 DAY 
                         ORDER BY created_at DESC LIMIT 1");

$latest_announcement = ($ann_res && $ann_res->num_rows > 0) ? $ann_res->fetch_assoc() : null;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = strtolower($_SESSION['role']);
$fullname = $_SESSION['fullname'];

// Fetch announcements joined with clubs to get the club name/color if available
$announcements_res = $conn->query("
    SELECT a.*, c.club_name, c.hex_color 
    FROM announcements a 
    LEFT JOIN clubs c ON a.club_id = c.id 
    ORDER BY a.created_at DESC
");

/* ===============================
    GET REAL COUNTS (UNREAD ONLY)
================================ */

// Count only UNREAD notifications
$notif_count = 0;
$notif_result = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE user_id = '$user_id' AND is_read = 0");
if($notif_result){
    $notif_count = $notif_result->fetch_assoc()['total'];
}

// Count only UNREAD messages
$msg_count = 0;
$msg_result = $conn->query("SELECT COUNT(*) as total FROM messages WHERE receiver_id = '$user_id' AND is_read = 0");
if($msg_result){
    $msg_count = $msg_result->fetch_assoc()['total'];
}

// Get latest notifications for initial load
$notifications = $conn->query("SELECT message, created_at, is_read FROM notifications WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT 5");

// FIXED QUERY: Join users table to get sender_name for the messages
$messages = $conn->query("
    SELECT u.fullname AS sender_name, m.message_text AS message, m.created_at, m.is_read 
    FROM messages m 
    JOIN users u ON m.sender_id = u.id 
    WHERE m.receiver_id = '$user_id' 
    ORDER BY m.created_at DESC 
    LIMIT 5
");
$clubs_query = $conn->query("SELECT * FROM clubs ORDER BY club_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ClubConnect - Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://unpkg.com/lucide@latest"></script>
   <style>
    :root {
        /* TOPBAR GRADIENT (Branding) */
        --brand-gradient: linear-gradient(-45deg, #0f02ff, #1b4ba5, #9b2f9f, #b31217, #e52d27);
        
        /* DEFAULT LIGHT MODE (White Body) */
        --bg-color: #ffffff;
        --text-color: #000000;
        --card-bg: #f8f9fa;
        --topbar-bg: rgba(255, 255, 255, 0.8);
        --input-bg: rgba(0, 0, 0, 0.05);
        --accent: #b31217;
    }

    /* DARK MODE (Greyish Blue Body) */
    /* This applies when the .light-mode class is NOT present */
    body:not(.light-mode) {
        --bg-color: #1a1c23; /* Very dark greyish blue */
        --text-color: #ffffff;
        --card-bg: #242731;
        --topbar-bg: rgba(0, 0, 0, 0.35);
        --input-bg: rgba(255, 255, 255, 0.1);
    }

    * { 
        margin: 0; 
        padding: 0; 
        box-sizing: border-box; 
        font-family: 'Segoe UI', sans-serif; 
        transition: background 0.3s ease, color 0.3s ease; 
    }

    body {
        background-color: var(--bg-color);
        color: var(--text-color);
        overflow-x: hidden;
        padding-top: 100px;
        min-height: 100vh;
    }

    @keyframes gradientMove {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 30px;
        
        /* Gradient Animation pinned to Topbar */
        background: var(--brand-gradient);
        background-size: 400% 400%;
        animation: gradientMove 12s ease infinite;
        
        backdrop-filter: blur(12px);
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }

    /* Keep Topbar elements white for contrast against the gradient */
    .topbar .logo, 
    .topbar .icon-wrapper, 
    .topbar .role-badge,
    .topbar span { 
        color: #ffffff !important; 
    }

    .topbar-right { display: flex; align-items: center; gap: 20px; }

    .user-profile-img {
        width: 40px; height: 40px; border-radius: 50%;
        object-fit: cover; border: 2px solid rgba(255, 255, 255, 0.5);
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    }

    .mod-indicators { display: flex; gap: 15px; align-items: center; }
    
    .icon-wrapper { 
        position: relative; 
        cursor: pointer; 
        color: #ffffff; 
        opacity: 0.9; 
        transition: 0.3s; 
    }
    .icon-wrapper:hover { opacity: 1; transform: translateY(-2px); }

    .badge {
        position: absolute; top: -5px; right: -8px;
        background: #ffffff; color: var(--accent);
        border-radius: 50%; padding: 2px 6px; font-size: 10px; font-weight: bold;
        display: inline-block;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .search-container { position: relative; margin: 20px auto; max-width: 500px; width: 90%; }
    .search-bar {
        width: 100%; padding: 12px 20px 12px 45px; border-radius: 30px;
        border: 1px solid rgba(128, 128, 128, 0.2); background: var(--input-bg);
        color: var(--text-color); outline: none; backdrop-filter: blur(5px);
    }
    .search-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); opacity: 0.6; }

    .dropdown { position: relative; }
    .dropdown-content {
        display: none; position: absolute; right: 0; top: 45px;
        background: var(--card-bg); min-width: 200px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.2); border-radius: 12px;
        overflow: hidden; z-index: 1001;
        border: 1px solid rgba(128,128,128,0.1);
    }
    .dropdown-content a, .dropdown-content div {
        color: var(--text-color); padding: 12px 16px; text-decoration: none;
        display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 14px;
    }
    .dropdown-content a:hover, .dropdown-content div:hover { background: rgba(179, 18, 23, 0.1); }
    .show { display: block; }

    .carousel-container { position:relative; padding:20px 0; max-width: 1400px; margin: 0 auto; }
    .carousel { display:flex; gap:25px; overflow-x:hidden; scroll-behavior:smooth; padding:40px 100px; }
    .card {
        width:280px; height:380px; border-radius:25px; overflow:hidden;
        position:relative; flex-shrink:0; transition:0.4s ease; 
        background: var(--card-bg); cursor: pointer;
        border: 1px solid rgba(128,128,128,0.1);
    }
    .card.hidden { display: none; }
    .card img { width:100%; height:100%; object-fit:cover; transition: 0.5s; }
    .card:hover img { transform: scale(1.1); }
    .card.active { transform: scale(1.08); border: 4px solid var(--accent); box-shadow: 0 15px 35px rgba(179, 18, 23, 0.3); }
    .card .overlay { position:absolute; bottom:0; width:100%; padding:20px; background:linear-gradient(to top, rgba(0,0,0,0.9), transparent); color: white; }

    .scroll-btn {
        position:absolute; top:50%; transform:translateY(-50%);
        width:55px; height:55px; border-radius:50%; border:none;
        cursor:pointer; z-index:100; background: var(--accent); color:white;
        display: flex; align-items: center; justify-content: center; font-size: 20px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }
    .left-btn { left: 20px; }
    .right-btn { right: 20px; }

    .icon-btn {
        background: rgba(255, 255, 255, 0.2); color: white; padding: 8px 15px;
        border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 10px; font-size: 14px;
        display: flex; align-items: center; gap: 5px; cursor: pointer;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 2000;
        left: 0; top: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.6);
        backdrop-filter: blur(5px);
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background: var(--card-bg);
        width: 400px;
        max-height: 500px;
        overflow-y: auto;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        border: 1px solid rgba(128,128,128,0.2);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        font-weight: bold;
        color: var(--text-color);
    }

    .close-btn { cursor: pointer; font-size: 18px; color: var(--accent); }

    .modal-item {
        padding: 12px;
        border-bottom: 1px solid rgba(128,128,128,0.2);
        font-size: 14px;
        color: var(--text-color);
    }

    .modal-item small { opacity: 0.6; display: block; margin-top: 5px; }

    .pulse { animation: pulseAnim 0.6s ease; }
    @keyframes pulseAnim {
        0%{transform:scale(1);}
        50%{transform:scale(1.3);}
        100%{transform:scale(1);}
    }

    .unread-item {
        border-left: 4px solid var(--accent);
        background: rgba(179,18,23,0.08);
    }

    /* Announcements and Banner Adjustments */
    .announcement-card {
        background: var(--card-bg);
        border-radius: 15px;
        padding: 25px;
        color: var(--text-color);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border: 1px solid rgba(128,128,128,0.1);
    }

    .toast-notification {
        position: fixed;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: var(--accent);
        color: white;
        padding: 12px 24px;
        border-radius: 50px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        gap: 15px;
        z-index: 6000;
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        cursor: pointer;
    }
    .toast-notification.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }
</style>
</head>
<body id="body">

<div class="topbar">
    <div class="logo" style="display:flex; align-items:center; gap:10px; font-weight:600;">
        <img src="/clubconnect/assetimages/ACLC.png" alt="Logo" style="height:35px;">
        <span>ClubConnect</span>
    </div>

    <div class="topbar-right">
        <?php if($user_role === 'moderator'): ?>
            <div class="mod-indicators">
                <div class="icon-wrapper" title="Notifications" onclick="openModal('notifModal')">
                    <i data-lucide="bell" size="22"></i>
                    <?php if($notif_count > 0): ?>
                        <span class="badge" id="notifBadge"><?php echo $notif_count; ?></span>
                    <?php else: ?>
                        <span class="badge" id="notifBadge" style="display:none;">0</span>
                    <?php endif; ?>
                </div>

                <div class="icon-wrapper" title="Messages" onclick="openModal('msgModal')">
                    <i data-lucide="mail" size="22"></i>
                    <?php if($msg_count > 0): ?>
                        <span class="badge" id="msgBadge"><?php echo $msg_count; ?></span>
                    <?php else: ?>
                        <span class="badge" id="msgBadge" style="display:none;">0</span>
                    <?php endif; ?>
                </div>
            </div>
            <div style="width:1px;height:25px;background:rgba(255,255,255,0.2);"></div>
        <?php endif; ?>

        <div class="role-badge" style="background:rgba(255,255,255,0.1); padding: 5px 12px; border-radius: 15px; font-size: 11px; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px;">
            <?php echo ucfirst($user_role); ?>
        </div>
        
        <img src="<?php echo $_SESSION['profile_pic'] ?? 'assetimages/default-user.png'; ?>" class="user-profile-img">

        <div class="dropdown">
            <button onclick="toggleDropdown()" class="icon-btn">
                <i data-lucide="settings" size="18"></i>
            </button>
            <div id="settingsDropdown" class="dropdown-content">
    <?php if ($user_role === 'admin'): ?>
        <a href="admin_dashboard.php" style="background: rgba(179, 18, 23, 0.1); font-weight: bold; color: var(--accent);">
            <i data-lucide="layout-dashboard" size="16"></i> Admin Dashboard
        </a>
        <hr style="border: 0.5px solid rgba(128,128,128,0.2);">
    <?php endif; ?>

    <a href="edit_profile.php"><i data-lucide="user-cog" size="16"></i> Account Settings</a>
    <a href="calendar.php"><i data-lucide="calendar" size="16"></i> Event Calendar</a>
    <div onclick="toggleDarkMode()"><i data-lucide="moon" size="16"></i> Toggle Theme</div>
    <hr style="border: 0.5px solid rgba(128,128,128,0.2);">
    <a href="logout.php" style="color: #ff4d4d;"><i data-lucide="log-out" size="16"></i> Logout</a>
</div>
            </div>
        </div>
    </div>
</div>
<?php if ($announcements_res && $announcements_res->num_rows > 0): ?>
<div class="announcement-slider">
    <div class="slides-container">
        <?php 
        $count = 0;
        while($ann = $announcements_res->fetch_assoc()): 
            $active = ($count === 0) ? 'active' : '';
            $headerColor = !empty($ann['hex_color']) ? $ann['hex_color'] : '#16a34a';
        ?>
            <div class="announcement-slide <?php echo $active; ?>" data-index="<?php echo $count; ?>">
                <div class="announcement-card" style="border-top: 5px solid <?php echo $headerColor; ?>;">
                    <div class="ann-header">
                        <span class="ann-tag" style="background: <?php echo $headerColor; ?>;">
                            <?php echo !empty($ann['club_name']) ? htmlspecialchars($ann['club_name']) : 'GLOBAL BROADCAST'; ?>
                        </span>
                        <small class="ann-date"><?php echo date("M d, Y", strtotime($ann['created_at'])); ?></small>
                    </div>
                    <h2><?php echo htmlspecialchars($ann['title']); ?></h2>
                    <p><?php echo nl2br(htmlspecialchars($ann['message'])); ?></p>
                </div>
            </div>
        <?php 
        $count++;
        endwhile; 
        ?>
    </div>

    <?php if ($count > 1): ?>
    <div class="slider-controls">
        <button onclick="moveSlide(-1)" class="control-btn">❮</button>
        <button onclick="moveSlide(1)" class="control-btn">❯</button>
    </div>
    <div class="slider-dots">
        <?php for($i=0; $i<$count; $i++): ?>
            <span class="dot <?php echo ($i===0)?'active':''; ?>" onclick="goToSlide(<?php echo $i; ?>)"></span>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<style>
/* --- UPDATED SLIDER CONTROLS --- */
.announcement-slider {
    position: relative;
    max-width: 850px;
    margin: 20px auto;
    /* overflow: visible ensures buttons can sit outside if needed */
    overflow: visible; 
    padding: 10px 60px;
}

/* --- THE FIX: Hide all slides by default --- */
.announcement-slide {
    display: none; /* This ensures they don't stack */
    width: 100%;
}

/* --- THE FIX: Only show the one with the .active class --- */
.announcement-slide.active {
    display: block;
    animation: fadeEffect 0.5s ease-out;
}

@keyframes fadeEffect {
    from { opacity: 0; transform: scale(0.98); }
    to { opacity: 1; transform: scale(1); }
}

.announcement-card {
    background: var(--card-bg);
    color: var(--text-color);
    border-radius: 15px;
    padding: 25px;
    border: 1px solid rgba(128, 128, 128, 0.2);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    /* Ensure the card takes up full width of the slide */
    width: 100%;
}
.ann-header { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin-bottom: 15px; 
}

.ann-tag { 
    padding: 4px 10px; 
    border-radius: 5px; 
    font-size: 11px; 
    font-weight: bold; 
    background: var(--accent); /* Uses your brand red */
    color: white; 
}

.ann-date { 
    opacity: 0.6; 
    font-size: 12px; 
    color: var(--text-color); 
}

.slider-controls {
    position: absolute;
    top: 50%;
    width: 100%;
    display: flex;
    justify-content: space-between;
    left: 0;
    transform: translateY(-50%);
    pointer-events: none;
}

.control-btn {
    pointer-events: auto;
    background: var(--accent); /* Red buttons match your theme better than grey */
    color: white;
    border: none;
    width: 45px;
    height: 45px;
    cursor: pointer;
    border-radius: 50%;
    margin: 0 5px;
    transition: 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

.control-btn:hover { 
    opacity: 0.9;
    transform: scale(1.1);
}

.slider-dots { 
    text-align: center; 
    margin-top: 15px; 
}

.dot {
    height: 8px; 
    width: 8px;
    margin: 0 4px;
    /* Fixed: Dots change color based on theme */
    background-color: var(--text-color); 
    opacity: 0.2;
    border-radius: 50%;
    display: inline-block;
    cursor: pointer;
    transition: 0.3s;
}

.dot.active { 
    background-color: var(--accent); 
    width: 20px; 
    border-radius: 10px; 
    opacity: 1;
}
</style>

<div class="hero">
    <h1 style="text-align:center; font-size: 2.8rem; margin-top: 20px; font-weight: 800;">Explore School Clubs</h1>
    <div class="search-container">
        <i data-lucide="search" class="search-icon" size="20"></i>
        <input type="text" id="searchInput" class="search-bar" placeholder="Search clubs by name..." onkeyup="filterClubs()">
    </div>
</div>

<div class="carousel-container">
    <button class="scroll-btn left-btn" onclick="scrollCarousel(-1)">❮</button>
    <div class="carousel" id="clubCarousel">
        <?php 
        $first = true; 
        if ($clubs_query && $clubs_query->num_rows > 0):
            while($club = $clubs_query->fetch_assoc()): 
                
                // 1. Get the Logo value from DB
                $logo_val = $club['logo'];
                
                // 2. Determine the correct URL path
                if (empty($logo_val)) {
                    // Default if no logo is uploaded
                    $image_src = "/clubconnect/assetimages/default-banner.jpg";
                } else {
                    // Remove any accidental backslashes from Windows paths and get just the filename
                    $filename = basename($logo_val); 
                    $image_src = "/clubconnect/assetimages/" . $filename;
                }
        ?>
            <div class="card <?php echo $first ? 'active' : ''; ?>" 
                 data-name="<?php echo htmlspecialchars($club['club_name']); ?>" 
                 onclick="location.href='club_home.php?id=<?php echo $club['id']; ?>'">
                
                <img src="<?php echo $image_src; ?>" 
                     alt="<?php echo htmlspecialchars($club['club_name']); ?>"
                     onerror="this.src='/clubconnect/assetimages/default-banner.jpg';">
                
                <div class="overlay">
                    <h3><?php echo htmlspecialchars($club['club_name']); ?></h3>
                    <p><?php echo htmlspecialchars($club['description']); ?></p>
                </div>
            </div>
        <?php 
                $first = false; 
            endwhile; 
        else:
            echo "<p style='padding: 20px;'>No clubs found.</p>";
        endif;
        ?>
    </div>
    <button class="scroll-btn right-btn" onclick="scrollCarousel(1)">❯</button>
</div>

<div id="notifModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span>Notifications</span>
            <span class="close-btn" onclick="closeModal('notifModal')">✖</span>
        </div>
        <div id="notifContainer">
            <?php if($notifications && $notifications->num_rows > 0): ?>
                <?php while($row = $notifications->fetch_assoc()): ?>
                    <div class="modal-item <?php echo $row['is_read']==0 ? 'unread-item' : ''; ?>">
                        <?php echo htmlspecialchars($row['message']); ?>
                        <small><?php echo $row['created_at']; ?></small>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="modal-item">No notifications yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="msgModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span>Inbox</span>
            <span class="close-btn" onclick="closeModal('msgModal')">✖</span>
        </div>
        <div id="msgContainer">
            <?php if($messages && $messages->num_rows > 0): ?>
                <?php while($row = $messages->fetch_assoc()): ?>
                    <div class="modal-item <?php echo $row['is_read']==0 ? 'unread-item' : ''; ?>">
                        <strong><?php echo htmlspecialchars($row['sender_name']); ?></strong><br>
                        <?php echo htmlspecialchars($row['message']); ?>
                        <small><?php echo $row['created_at']; ?></small>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="modal-item">No messages yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="globalToast" class="toast-notification">
	<i data-lucide="bell" size="20"></i>
	<div id="toastContent" style="display:flex; flex-direction:column; gap:2px;">
		<strong id="toastTitle" style="font-size:14px;">Title</strong>
		<span id="toastDesc" style="font-size:12px; opacity:0.9;">Description</span>
	</div>
</div>

<script>
    lucide.createIcons();

    // Carousel Logic
    const carousel = document.getElementById("clubCarousel");
    let cards = document.querySelectorAll(".card");
    let currentIndex = 0;

    function scrollCarousel(direction) {
        const visibleCards = Array.from(cards).filter(c => !c.classList.contains('hidden'));
        if (visibleCards.length === 0) return;
        currentIndex += direction;
        if (currentIndex < 0) currentIndex = 0;
        if (currentIndex >= visibleCards.length) currentIndex = visibleCards.length - 1;
        const cardWidth = visibleCards[0].offsetWidth + 25;
        carousel.scrollTo({ left: cardWidth * currentIndex, behavior: "smooth" });
        updateActiveCard(visibleCards);
    }

    function updateActiveCard(visibleList) {
        cards.forEach(c => c.classList.remove("active"));
        if (visibleList[currentIndex]) visibleList[currentIndex].classList.add("active");
    }

    function filterClubs() {
        const query = document.getElementById("searchInput").value.toLowerCase();
        cards.forEach(card => {
            const name = card.getAttribute("data-name").toLowerCase();
            card.classList.toggle("hidden", !name.includes(query));
        });
        currentIndex = 0;
        carousel.scrollTo({ left: 0 });
        const visibleCards = Array.from(cards).filter(c => !c.classList.contains('hidden'));
        updateActiveCard(visibleCards);
    }

    // Dropdown & Theme Logic
    function toggleDropdown() { document.getElementById("settingsDropdown").classList.toggle("show"); }

    window.onclick = function(event) {
        if (!event.target.closest('.dropdown')) {
            const dropdown = document.getElementById("settingsDropdown");
            if(dropdown) dropdown.classList.remove("show");
        }
        const modals = document.querySelectorAll(".modal");
        modals.forEach(modal => {
            if(event.target === modal) modal.style.display = "none";
        });
    }

    function toggleDarkMode() {
        const body = document.getElementById("body");
        body.classList.toggle("light-mode");
        localStorage.setItem("theme", body.classList.contains("light-mode") ? "light" : "dark");
    }

    window.onload = () => {
        if (localStorage.getItem("theme") === "light") document.getElementById("body").classList.add("light-mode");

    };

    // Modal & Real-time Logic
    let lastNotifCount = <?php echo $notif_count; ?>;
    let lastMsgCount = <?php echo $msg_count; ?>;

    function openModal(id){
        document.getElementById(id).style.display = "flex";
        if(id === "notifModal" || id === "msgModal") {
            fetch("mark_read.php?type=" + (id === "notifModal" ? "notif" : "msg"))
                .then(() => { setTimeout(fetchNotifications, 500); });
        }
    }

    function closeModal(id){
        document.getElementById(id).style.display = "none";
    }

    // function to trigger the toast pop-up
    function showToast(title, desc, modalId) {
        const toast = document.getElementById('globalToast');
        document.getElementById('toastTitle').innerText = title;

        // truncate long messages so the toast doesn't get huge
        document.getElementById('toastDesc').innerText = desc.length > 40 ? desc.substring(0, 40) + '...' : desc;

        toast.onclick = () => {
            toast.classList.remove('show');
            openModal(modalId);
        };

        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 5000); // hide after 5 seconds
    }

    function fetchNotifications(){
        fetch("fetch_notifications.php")
            .then(res => res.json())
            .then(data => {
                // safeguard: check for both camelCase and snake_case, default to 0
                const currentUnreadNotifs = data.unreadNotifs || data.unread_notifs || 0;
                const currentUnreadMsgs = data.unreadMsgs || data.unread_msgs || 0;

                // --- HANDLE NOTIFICATIONS ---
                const nBadge = document.getElementById("notifBadge");
                if (nBadge) {
                    if(currentUnreadNotifs > 0){
                        nBadge.style.display = "inline-block";
                        nBadge.innerText = currentUnreadNotifs;

                        if(currentUnreadNotifs > lastNotifCount) {
                            nBadge.classList.add("pulse");
                            setTimeout(() => nBadge.classList.remove("pulse"), 600);
                            if(data.notifications && data.notifications.length > 0) {
                                showToast("System Notification", data.notifications[0].message, 'notifModal');
                            }
                        }
                    } else { nBadge.style.display = "none"; }
                }
                lastNotifCount = currentUnreadNotifs;

                // --- HANDLE MESSAGES ---
                const mBadge = document.getElementById("msgBadge");
                if (mBadge) {
                    if(currentUnreadMsgs > 0){
                        mBadge.style.display = "inline-block";
                        mBadge.innerText = currentUnreadMsgs;

                        if(currentUnreadMsgs > lastMsgCount) {
                            mBadge.classList.add("pulse");
                            setTimeout(() => mBadge.classList.remove("pulse"), 600);
                            if(data.messages && data.messages.length > 0) {
                                showToast("New message from " + data.messages[0].sender_name, data.messages[0].message, 'msgModal');
                            }
                        }
                    } else { mBadge.style.display = "none"; }
                }
                lastMsgCount = currentUnreadMsgs;

                // --- UPDATE MODAL LISTS ---
                const nContainer = document.getElementById("notifContainer");
                if(nContainer) {
                    nContainer.innerHTML = data.notifications.length === 0 ? "<div class='modal-item'>No notifications yet.</div>" : "";
                    data.notifications.forEach(n => {
                        nContainer.innerHTML += `<div class="modal-item ${n.is_read==0?'unread-item':''}">
                        ${n.message}<br><small>${n.created_at}</small></div>`;
                    });
                }

                const mContainer = document.getElementById("msgContainer");
                if(mContainer) {
                    mContainer.innerHTML = data.messages.length === 0 ? "<div class='modal-item'>No messages yet.</div>" : "";
                    data.messages.forEach(m => {
                        mContainer.innerHTML += `<div class="modal-item ${m.is_read==0?'unread-item':''}">
                        <strong>${m.sender_name}</strong><br>${m.message}<br><small>${m.created_at}</small></div>`;
                    });
                }
            })
            .catch(err => console.error("Error fetching notifications:", err));
    }

    setInterval(fetchNotifications, 5000);

    let currentSlide = 0;
const slides = document.querySelectorAll('.announcement-slide');
const dots = document.querySelectorAll('.dot');

function moveSlide(step) {
    showSlide(currentSlide + step);
}

function goToSlide(index) {
    showSlide(index);
}

function showSlide(n) {
    if (slides.length === 0) return;
    
    // Hide current
    slides[currentSlide].classList.remove('active');
    dots[currentSlide].classList.remove('active');
    
    // Calculate next index (loops around)
    currentSlide = (n + slides.length) % slides.length;
    
    // Show next
    slides[currentSlide].classList.add('active');
    dots[currentSlide].classList.add('active');
}

// Optional: Auto-play every 7 seconds
setInterval(() => moveSlide(1), 7000);
</script>
</body>
</html>