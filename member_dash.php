<?php

session_start();
include('config.php');

if (!isset($_SESSION['member_id'])) {
    header("Location: member_login.php");
    exit();
}

$member_id = $_SESSION['member_id'];
$name = $_SESSION['member_name'];

// Fetch full member details
$query = "SELECT * FROM members WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();




if (!isset($_SESSION['member_id'])) {
    header("Location: member_login.php");
    exit();
}

include 'config.php'; // your DB connection

$member_id = $_SESSION['member_id'];

// Fetch member data
$member_query = $conn->prepare("SELECT name, email, age, gender, height, weight, membership_plan, membership_expiry, trainer_id, profile_pic, calorie_goal FROM members WHERE id=?");
$member_query->bind_param("i", $member_id);
$member_query->execute();
$member = $member_query->get_result()->fetch_assoc();

// Fetch trainer info
$trainer_query = $conn->prepare("SELECT name, specialization, phone FROM trainers WHERE id=?");
$trainer_query->bind_param("i", $member['trainer_id']);
$trainer_query->execute();
$trainer = $trainer_query->get_result()->fetch_assoc();

// Fetch upcoming sessions
$sessions_query = $conn->prepare("SELECT class_name, class_date, class_time FROM sessions WHERE member_id=? ORDER BY class_date ASC LIMIT 3");
$sessions_query->bind_param("i", $member_id);
$sessions_query->execute();
$sessions = $sessions_query->get_result();

// Fetch notifications/offers
$notif_query = $conn->prepare("SELECT title, message, created_at FROM notifications WHERE member_id=? OR member_id IS NULL ORDER BY created_at DESC LIMIT 5");
$notif_query->bind_param("i", $member_id);
$notif_query->execute();
$notifications = $notif_query->get_result();

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_pic'])) {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir);
    $fileName = basename($_FILES['profile_pic']['name']);
    $targetFile = $targetDir . time() . "_" . $fileName;
    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFile)) {
        $update_pic = $conn->prepare("UPDATE members SET profile_pic=? WHERE id=?");
        $update_pic->bind_param("si", $targetFile, $member_id);
        $update_pic->execute();
        header("Location: member_dash.php");
        exit();
    }
}

// Handle calorie tracker
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['calories'])) {
    $cal = $_POST['calories'];
    $insert_cal = $conn->prepare("INSERT INTO calorie_log (member_id, calories, date) VALUES (?, ?, CURDATE())");
    $insert_cal->bind_param("ii", $member_id, $cal);
    $insert_cal->execute();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Member Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body { background: #f4f6f8; font-family: 'Poppins', sans-serif; }
.sidebar { height: 100vh; background: #222; color: #fff; position: fixed; width: 250px; transition: all 0.3s; }
.sidebar a { color: #ddd; text-decoration: none; display: block; padding: 15px 20px; border-bottom: 1px solid #333; }
.sidebar a:hover { background: #444; }
.content { margin-left: 250px; padding: 30px; }
.card { border-radius: 15px; }
img.profile { width: 130px; height: 130px; border-radius: 50%; object-fit: cover; border: 3px solid #ccc; }
</style>
</head>

<body>

    


<!-- Sidebar -->
<div class="sidebar">
    <h4 class="text-center py-3 border-bottom">🏋️ Gym Dashboard</h4>
    <a href="#" onclick="showSection('home')">🏠 Home</a>
    <a href="#" onclick="showSection('profile')">👤 Profile</a>
    <a href="#" onclick="showSection('membership')">💳 Membership</a>
    <a href="#" onclick="showSection('sessions')">📅 Sessions</a>
    <a href="#" onclick="showSection('trainer')">💪 Trainer</a>
    <a href="#" onclick="showSection('progress')">📈 Progress</a>
    <a href="#" onclick="showSection('bmi')">⚖️ BMI</a>
    <a href="#" onclick="showSection('calorie')">🔥 Calorie Tracker</a>
    <a href="#" onclick="showSection('notifications')">🔔 Notifications</a>
    <a href="member_logout.php" class="text-danger">🚪 Logout</a>
</div>

<div class="content">

<!-- HOME -->
<div id="home" class="section">
    <h2>Welcome back, <?= htmlspecialchars($member['name']) ?> 👋</h2>
    <p>Keep going — your goals are waiting!</p>
    <?php if ($member['profile_pic']): ?>
        <img src="<?= htmlspecialchars($member['profile_pic']) ?>" class="profile mt-3 shadow">
    <?php else: ?>
        <img src="https://cdn-icons-png.flaticon.com/512/1946/1946429.png" class="profile mt-3 shadow">
    <?php endif; ?>
</div>

<!-- PROFILE -->
<div id="profile" class="section" style="display:none;">
    <h3>👤 Profile Summary</h3>
    <div class="card p-3 mt-3">
        <form method="POST" enctype="multipart/form-data">
            <label><b>Profile Picture:</b></label><br>
            <input type="file" name="profile_pic" class="form-control mb-3" accept="image/*">
            <button class="btn btn-sm btn-primary">Upload</button>
        </form>
        <hr>
        <p><b>Name:</b> <?= htmlspecialchars($member['name']) ?></p>
        <p><b>Email:</b> <?= htmlspecialchars($member['email']) ?></p>
        <p><b>Age:</b> <?= htmlspecialchars($member['age']) ?></p>
        <p><b>Gender:</b> <?= htmlspecialchars($member['gender']) ?></p>
        <p><b>Height:</b> <?= htmlspecialchars($member['height']) ?> cm</p>
        <p><b>Weight:</b> <?= htmlspecialchars($member['weight']) ?> kg</p>
    </div>
</div>

<!-- MEMBERSHIP -->
<div id="membership" class="section" style="display:none;">
    <h3>💳 Membership Plan</h3>
    <div class="card p-3 mt-3">
        <p><b>Plan:</b> <?= htmlspecialchars($member['membership_plan']) ?></p>
        <p><b>Expires on:</b> <?= htmlspecialchars($member['membership_expiry']) ?></p>
        <?php
        $days_left = ceil((strtotime($member['membership_expiry']) - time()) / (60*60*24));
        echo "<p><b>Days Left:</b> {$days_left} days</p>";
        ?>
    </div>
</div>

<!-- SESSIONS -->
<div id="sessions" class="section" style="display:none;">
    <h3>📅 Upcoming Sessions</h3>
    <?php if ($sessions->num_rows > 0): ?>
    <table class="table table-striped mt-3">
        <thead><tr><th>Class</th><th>Date</th><th>Time</th></tr></thead>
        <tbody>
        <?php while($s = $sessions->fetch_assoc()): ?>
        <tr><td><?= htmlspecialchars($s['class_name']) ?></td><td><?= $s['class_date'] ?></td><td><?= $s['class_time'] ?></td></tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p class="text-muted mt-3">No upcoming sessions.</p>
    <?php endif; ?>
</div>

<!-- TRAINER -->
<div id="trainer" class="section" style="display:none;">
    <h3>💪 Trainer Info</h3>
    <?php if ($trainer): ?>
    <div class="card p-3 mt-3">
        <p><b>Name:</b> <?= htmlspecialchars($trainer['name']) ?></p>
        <p><b>Specialization:</b> <?= htmlspecialchars($trainer['specialization']) ?></p>
        <p><b>Contact:</b> <?= htmlspecialchars($trainer['phone']) ?></p>
    </div>
    <?php else: ?>
    <p class="text-muted mt-3">No trainer assigned yet.</p>
    <?php endif; ?>
</div>

<!-- PROGRESS -->
<div id="progress" class="section" style="display:none;">
    <h3>📈 Fitness Progress Tracker</h3>
    <canvas id="progressChart" class="mt-3"></canvas>
</div>

<!-- BMI -->
<div id="bmi" class="section" style="display:none;">
    <h3>⚖️ BMI Calculator</h3>
    <div class="card p-3 mt-3">
        <div class="mb-3">
            <label>Height (cm):</label>
            <input type="number" id="bmiHeight" class="form-control" value="<?= $member['height'] ?>">
        </div>
        <div class="mb-3">
            <label>Weight (kg):</label>
            <input type="number" id="bmiWeight" class="form-control" value="<?= $member['weight'] ?>">
        </div>
        <button class="btn btn-primary" onclick="calculateBMI()">Calculate</button>
        <p id="bmiResult" class="mt-3 fw-bold"></p>
    </div>
</div>

<!-- CALORIE TRACKER -->
<div id="calorie" class="section" style="display:none;">
    <h3>🔥 Daily Calorie Tracker</h3>
    <div class="card p-3 mt-3">
        <form method="POST">
            <label><b>Enter calories consumed today:</b></label>
            <input type="number" name="calories" class="form-control mb-2" required>
            <button class="btn btn-success btn-sm">Add</button>
        </form>
        <hr>
        <p><b>Your Daily Goal:</b> <?= htmlspecialchars($member['calorie_goal'] ?? '2000') ?> kcal</p>
        <canvas id="calorieChart"></canvas>
    </div>
</div>

<!-- NOTIFICATIONS -->
<div id="notifications" class="section" style="display:none;">
    <h3>🔔 Notifications & Offers</h3>
    <?php if ($notifications->num_rows > 0): ?>
        <?php while($n = $notifications->fetch_assoc()): ?>
            <div class="alert alert-info mt-3">
                <b><?= htmlspecialchars($n['title']) ?></b><br>
                <?= htmlspecialchars($n['message']) ?><br>
                <small class="text-muted"><?= $n['created_at'] ?></small>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="text-muted mt-3">No notifications.</p>
    <?php endif; ?>
</div>

</div>

<script>
// Sidebar navigation
function showSection(id) {
    document.querySelectorAll('.section').forEach(sec => sec.style.display = 'none');
    document.getElementById(id).style.display = 'block';
}

// Progress Chart
new Chart(document.getElementById('progressChart'), {
    type: 'line',
    data: {
        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
        datasets: [{ label: 'Weight (kg)', data: [72,71,70,68], borderWidth: 3 }]
    }
});

// Calorie Chart Example
new Chart(document.getElementById('calorieChart'), {
    type: 'doughnut',
    data: {
        labels: ['Consumed', 'Remaining'],
        datasets: [{
            data: [1500, 500],
            borderWidth: 1
        }]
    }
});

// BMI Calculator
function calculateBMI() {
    const h = parseFloat(document.getElementById('bmiHeight').value) / 100;
    const w = parseFloat(document.getElementById('bmiWeight').value);
    const bmi = (w / (h * h)).toFixed(2);
    let msg = `Your BMI is ${bmi}. `;
    if (bmi < 18.5) msg += "Underweight — eat more protein.";
    else if (bmi < 24.9) msg += "Healthy — keep going!";
    else if (bmi < 29.9) msg += "Overweight — increase cardio.";
    else msg += "Obese — consult trainer.";
    document.getElementById('bmiResult').innerText = msg;
}
</script>
</body>
</html>
