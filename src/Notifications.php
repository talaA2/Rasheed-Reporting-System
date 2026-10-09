<?php
require_once __DIR__ . "/session.php";
include "db.php";

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$residentID = $_SESSION['userID'];

$stmt = $conn->prepare("
    SELECT notificationID, message, type, created_at, reportID
    FROM notification
    WHERE residentID = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $residentID);
$stmt->execute();
$result = $stmt->get_result();

function getNotificationIcon($type) {
    if ($type === "Completed") {
        return "fa-regular fa-circle-check";
    } elseif ($type === "In Progress") {
        return "fa-regular fa-clock";
    } elseif ($type === "Pending") {
        return "fa-regular fa-hourglass-half";
    } elseif ($type === "Deleted") {
        return "fa-regular fa-trash-can";
    } else {
        return "fa-regular fa-bell";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notifications - Rasheed</title>

<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="notifications-page">

<header class="topbar">
  <div class="container topbar-inner">

    <div class="brand">
      <a href="main.php"><img src="images/logo.png" alt="Logo"></a>
      <span class="brand-text"><a href="main.php">Rasheed</a></span>
    </div>

    <nav class="nav-links">
      <a href="AddReport.php" class="nav-link">
        <i class="fa-regular fa-file-lines"></i> Add Report
      </a>

      <a href="MyReports.php" class="nav-link">
        <i class="fa-regular fa-clipboard"></i> My Reports
      </a>

      <a href="Rewards.php" class="nav-link">
        <i class="fa-regular fa-star"></i> Rewards
      </a>

      <a href="Notifications.php" class="nav-link active">
        <i class="fa-regular fa-bell"></i> Notifications
      </a>

      <a href="logout.php" class="nav-link logout">
        <i class="fa-solid fa-right-from-bracket"></i> Log Out
      </a>
    </nav>

  </div>
</header>

<div class="container">

  <h1 class="page-title">Notifications</h1>
  <p class="helper-text">Stay updated with your reports and rewards!</p>

  <div class="notifications-list">

    <?php if ($result->num_rows > 0): ?>
      <?php while ($notification = $result->fetch_assoc()): ?>
        <div class="notification-card">
          <div class="notif-icon">
            <i class="<?php echo getNotificationIcon($notification['type']); ?>"></i>
          </div>

          <div class="notif-content">
            <p class="notif-title">
              <?php echo htmlspecialchars($notification['type'] ?? "Notification"); ?>
            </p>

            <p class="notif-desc">
              <?php echo htmlspecialchars($notification['message']); ?>
            </p>

            <span class="notif-time">
              <?php echo date("M d, Y - h:i A", strtotime($notification['created_at'])); ?>
            </span>
          </div>
        </div>
      <?php endwhile; ?>

    <?php else: ?>
      <div class="notification-card">
        <div class="notif-icon">
          <i class="fa-regular fa-bell"></i>
        </div>

        <div class="notif-content">
          <p class="notif-title">No Notifications</p>
          <p class="notif-desc">You do not have any notifications yet.</p>
        </div>
      </div>
    <?php endif; ?>

  </div>

</div>

<footer class="footer">
  <div class="footer-container">

    <div class="footer-left">
      <h3>Rasheed</h3>
      <p>Helping communities report water and electricity issues efficiently.</p>
    </div>

    <div class="footer-copy">
      <p>© 2026 Rasheed. All rights reserved.</p>
    </div>

  </div>
</footer>

</body>
</html>