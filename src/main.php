<?php
require_once __DIR__ . "/session.php";
include "db.php";
 if (!isset($_SESSION['userID']) || $_SESSION['role'] != 'resident') {
    header("Location: login.php?error=Access denied");
    exit();
}
$residentID = (int) $_SESSION['userID'];

$stmt1 = $conn->prepare("SELECT COUNT(*) AS total FROM report WHERE residentID = ? AND NOT (status = 'Deleted' AND deletedByUser = 1)");
$stmt1->bind_param("i", $residentID);
$stmt1->execute();
$totalReports = $stmt1->get_result()->fetch_assoc()['total'];

$stmt2 = $conn->prepare("SELECT points FROM resident WHERE residentID = ?");
$stmt2->bind_param("i", $residentID);
$stmt2->execute();
$points = $stmt2->get_result()->fetch_assoc()['points'] ?? 0;
$badgeStatus = ($points >= 100) ? "Unlocked" : "Locked";

$stmt3 = $conn->prepare("SELECT * FROM report WHERE residentID = ? AND NOT (status = 'Deleted' AND deletedByUser = 1) ORDER BY reportID DESC LIMIT 3");
$stmt3->bind_param("i", $residentID);
$stmt3->execute();
$reports = $stmt3->get_result();

$stmtUser = $conn->prepare("SELECT firstName, lastName FROM user WHERE userID = ?");
$stmtUser->bind_param("i", $residentID);
$stmtUser->execute();
$user = $stmtUser->get_result()->fetch_assoc();

$name = $user['firstName'] . " " . $user['lastName'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Rasheed Dashboard</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
    rel="stylesheet"
  />

  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
  />

  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <!-- Navbar -->
 <header class="topbar">
    <div class="container topbar-inner">
      <div class="brand">
        <img src="images/logo.png" alt="Rasheed Logo">
        <span class="brand-text">Rasheed</span>
      </div>

      <nav class="nav-links">
        <a href="AddReport.php" class="nav-link "><i class="fa-regular fa-file-lines"></i> Add Report</a>
        <a href="MyReports.php" class="nav-link"><i class="fa-regular fa-clipboard"></i> My Reports</a>
        <a href="Rewards.php" class="nav-link"><i class="fa-regular fa-star"></i> Rewards</a>
        <a href="Notifications.php" class="nav-link"><i class="fa-regular fa-bell"></i> Notifications</a>
        <a href="logout.php" class="nav-link logout"><i class="fa-solid fa-right-from-bracket"></i> Log Out</a>
      </nav>
    </div>
  </header>

  <!-- Hero -->
  <section class="hero">
    <div class="container hero-content">
      <h1>Welcome back, <?= e($name) ?>!</h1>
      <p>Here's an overview of your reporting activity.</p>
    </div>
    <div class="hero-curve"></div>
  </section>

  <!-- Main content -->
  <main class="container main-content">
    <section class="stats">
      <div class="card stat-card">
        <div>
          <p>Total Reports</p>
          <h2><?= $totalReports ?></h2>
        </div>
        <div class="icon-box green-box">
          <i class="fa-regular fa-file-lines"></i>
        </div>
      </div>

      <div class="card stat-card">
        <div>
          <p>Points Earned</p>
          <h2><?= $points ?></h2>
        </div>
        <div class="icon-box blue-box">
          <i class="fa-regular fa-star"></i>
        </div>
      </div>

      <div class="card stat-card">
        <div>
          <p>Badge Status</p>
          <h2><?= $badgeStatus?></h2>
        </div>
        <div class="icon-box <?= ($points >= 100) ? 'green-box' : 'gray-box' ?>">
          <i class="fa-solid fa-trophy"></i>
        </div>
      </div>
    </section>

    <section class="reports-section">
  <div class="section-header">
    <h2>Recent Reports</h2>
    <a href="MyReports.php" class="view-all">View all →</a>
  </div>

<div class="reports-box" style="display:flex; flex-direction:column; width:100%;">

<?php if ($reports->num_rows == 0): ?>

<div class="empty-state">
  <div class="empty-icon">📋</div>
  <p>No reports yet.</p>
  <a href="AddReport.php" class="submit-link">Add a report →</a>
</div>

<?php else: ?>

<?php while($row = $reports->fetch_assoc()): ?>

<a href="report-det.php?id=<?= e($row['reportID']) ?>" class="report-card">

  <div class="icon">
    <?= $row['type'] == "Water" ? "💧" : "⚡" ?>
  </div>

  <div class="info">
    <div class="top">
      <b>RPT-<?= e($row['reportID']) ?></b>
      <span class="badge <?= e(strtolower($row['severity'])) ?>">
        <?= e($row['severity']) ?>
      </span>
    </div>

    <div class="meta"><?= e($row['description']) ?></div>
    <div class="meta"><?= e($row['city']) ?></div>
  </div>

  <div class="status-text <?= e(strtolower($row['status'])) ?>">
    <?= e($row['status']) ?>
  </div>

</a>

<hr>

<?php endwhile; ?>

<?php endif; ?>

  </div>
</section>
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
