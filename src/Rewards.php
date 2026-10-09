<?php
require_once __DIR__ . "/session.php";
include "db.php";

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$residentID = $_SESSION['userID'];

/* Get resident points */
$points = 0;

$stmt = $conn->prepare("SELECT points FROM resident WHERE residentID = ?");
$stmt->bind_param("i", $residentID);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $points = $row['points'];
}

$badgeUnlocked = $points >= 100;

/* Get completed reports for points history */
$historyStmt = $conn->prepare("
    SELECT reportID, created_at 
    FROM report 
    WHERE residentID = ? AND status = 'Completed'
    ORDER BY created_at DESC
");
$historyStmt->bind_param("i", $residentID);
$historyStmt->execute();
$historyResult = $historyStmt->get_result();

$progressDegree = min($points, 100) * 3.6;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Rewards - Rasheed</title>

  <link rel="stylesheet" href="style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

  <style>
    .progress-circle {
      background: conic-gradient(
        #22a06b <?php echo $progressDegree; ?>deg,
        #e9ecef <?php echo $progressDegree; ?>deg
      );
    }
  </style>
</head>

<body class="rewards-page">

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

      <a href="Rewards.php" class="nav-link active">
        <i class="fa-regular fa-star"></i> Rewards
      </a>

      <a href="Notifications.php" class="nav-link">
        <i class="fa-regular fa-bell"></i> Notifications
      </a>

      <a href="logout.php" class="nav-link logout">
        <i class="fa-solid fa-right-from-bracket"></i> Log Out
      </a>
    </nav>

  </div>
</header>

<main class="page-wrapper">
  <div class="rewards-container">
    <h1 class="page-title">Rewards</h1>
    <p class="page-subtitle">Track your points and earn badges.</p>

    <section class="reward-card progress-card">
      <div class="progress-circle">
        <div class="progress-inner">
          <span class="points-number" id="pointsValue"><?php echo $points; ?></span>
          <span class="points-total">/ 100 pts</span>
        </div>
      </div>
      <p class="progress-note">10 points awarded per verified report</p>
    </section>

    <section class="reward-card badge-card">
      <div class="badge-icon">
        <?php if ($badgeUnlocked): ?>
          <i class="fa-solid fa-award"></i>
        <?php else: ?>
          <i class="fa-solid fa-lock"></i>
        <?php endif; ?>
      </div>

      <h2 class="badge-title">
        <?php echo $badgeUnlocked ? "Badge Unlocked" : "Badge Locked"; ?>
      </h2>

      <p class="badge-text">
        <?php if ($badgeUnlocked): ?>
          You have unlocked your<br>
          Community Guardian badge.
        <?php else: ?>
          Complete 10 verified reports to unlock your<br>
          Community Guardian badge.
        <?php endif; ?>
      </p>
    </section>

    <section class="reward-card">
      <h3 class="history-title">Points History</h3>

      <?php if ($historyResult->num_rows > 0): ?>
        <?php while ($report = $historyResult->fetch_assoc()): ?>
        <div class="history-item">
            <p>
              <strong>+10 points</strong>
              awarded for completed report #<?php echo $report['reportID']; ?>
            </p>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="history-empty">
          No points earned yet. Submit and get reports verified to earn points.
        </p>
      <?php endif; ?>
    </section>
  </div>
</main>

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