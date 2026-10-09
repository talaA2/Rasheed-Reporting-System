<?php
require_once __DIR__ . "/session.php";
include "db.php";

if (!isset($_SESSION['role'])) {
  header("Location: login.php");
  exit();
}

if (!isset($_GET['id'])) {
  echo "No report selected";
  exit();
}

$id = intval($_GET['id']);


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {

  $delete_id = intval($_POST['delete_id']);
  $ownerID = (int) $_SESSION['userID'];

  // Residents can only delete their own reports that are not completed
  $del = $conn->prepare("UPDATE report
                         SET status = 'Deleted', deletedByUser = 1
                         WHERE reportID = ? AND residentID = ? AND status <> 'Completed'");
  $del->bind_param("ii", $delete_id, $ownerID);
  $del->execute();

  header("Location: MyReports.php");
  exit();
}

$stmt = $conn->prepare("SELECT * FROM report WHERE reportID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
  echo "Report not found";
  exit();
}

if ($_SESSION['role'] == 'resident' && $row['residentID'] != $_SESSION['userID']) {
  echo "Access denied";
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Report Details</title>

<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">


<style>
.details-box {
  background: white;
  border-radius: 16px;
  padding: 25px;
  margin-top: 30px;
}

.location-table {
  width: 100%;
  margin-top: 15px;
  border-collapse: collapse;
}

.location-table td {
  padding: 12px;
  border-bottom: 1px solid #eee;
}

.label {
  color: gray;
  font-size: 13px;
}

.photo-box {
  margin-top: 15px;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #eee;
}

.photo-box img {
  width: 100%;
}

.actions {
  display: flex;
  gap: 15px;
  margin-top: 25px;
}

  .back-btn {
margin-left: 10px;
margin-top: 10px;
}

.btn-danger {
  background: white;
  border: 2px solid #e57373;
  color: #d33;
}
</style>

</head>

<body>
<?php if ($_SESSION['role'] == 'admin'): ?>

<header class="topbar">
  <div class="container topbar-inner">

    <div class="brand">
      <a href="admin.php">
        <img src="images/logo.png" alt="Logo">
      </a>
      <span class="brand-text">
        <a href="admin.php">Rasheed</a>
      </span>
    </div>

    <nav class="nav-links">
      <a href="logout.php" class="nav-link logout">
        <i class="fa-solid fa-right-from-bracket"></i> Log Out
      </a>
    </nav>

  </div>
</header>

<?php else: ?>

<header class="topbar">
  <div class="container topbar-inner">

    <div class="brand">
      <a href="main.php">
        <img src="images/logo.png" alt="Logo">
      </a>
      <span class="brand-text">
        <a href="main.php">Rasheed</a>
      </span>
    </div>

    <nav class="nav-links">
      <a href="AddReport.php" class="nav-link">
        <i class="fa-regular fa-file-lines"></i> Add Report
      </a>

      <a href="MyReports.php" class="nav-link active">
        <i class="fa-regular fa-clipboard"></i> My Reports
      </a>

      <a href="Rewards.php" class="nav-link">
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

<?php endif; ?>

<div class="container main-content" >
  <h1 class="page-title">Report Details</h1>
  
  <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
  <p class="edit-success-message">Report updated successfully!</p>
<?php endif; ?>

  <?php if ($_SESSION['role'] == 'admin'): ?>
<a href="admin.php" class="back-btn">⬅ Back</a>
<?php else: ?>
<a href="MyReports.php" class="back-btn">⬅ Back</a>
<?php endif; ?>

  <div class="details-box">

    <!-- TITLE -->
    <div style="display:flex; align-items:center; gap:10px;">
      <div class="icon">
       <?= $row['type'] == "Water" ? "💧" : "⚡" ?>
    </div>
      <div>
        <h2 style="margin:0;">RPT-<?= e($row['reportID']) ?></h2>
        <span style="color:gray;"><?= e($row['type']) ?>  Issue</span>
      </div>
    </div>

    <!-- STATUS -->
    <div style="margin-top:15px;">
      <span class="badge" style="background:#eee;"><?= e($row['status']) ?></span>
      <span class="badge <?= e(strtolower($row['severity'])) ?>"> <?= e($row['severity']) ?></span>
    </div>

    <!-- DESCRIPTION -->
    <div style="margin-top:20px;">
      <p class="label">DESCRIPTION</p>
      <p><?= e($row['description']) ?></p>
    </div>

    <!-- LOCATION -->
    <div style="margin-top:20px;">
      <p class="label">LOCATION</p>

      <table class="location-table">
        <tr>
          <td class="label">City</td>
          <td><?= e($row['city']) ?></td>
        </tr>
        <tr>
          <td class="label">Neighborhood</td>
          <td><?= e($row['neighborhood']) ?></td>
        </tr>
        <tr>
          <td class="label">Street</td>
          <td><?= e($row['street']) ?></td>
        </tr>
        <tr>
          <td class="label">Building</td>
          <td><?= e($row['building_no']) ?></td>
        </tr>
      </table>
    </div>

    <!-- DATE -->
    <div style="margin-top:20px;">
      <p class="label">SUBMITTED</p>
      <p><?= e($row['created_at']) ?></p>
    </div>

    <!-- PHOTO -->
    <div style="margin-top:20px;">
      <p class="label">PHOTO</p>

      <div class="photo-box">
        <?php if (!empty($row['image'])): ?>
          <img src="uploads/<?= e($row['image']) ?>" alt="Report photo">
        <?php else: ?>
          <span class="info-note">No photo was attached.</span>
        <?php endif; ?>
      </div>
    </div>

    <!-- ACTIONS -->
    <div class="actions">

<?php if ($_SESSION['role'] == 'admin'): ?>

  <select class="btn">
    <option>Pending</option>
    <option>In Progress</option>
    <option>Completed</option>
  </select>

<?php else: ?>

  <?php if (!($row['status'] == 'Deleted')&&!($row['status'] == 'Completed')): ?>

    <button class="btn"
      onclick="window.location.href='EditReport.php?id=<?= e($row['reportID']) ?>'">
      ✏️ Edit Report
    </button>

    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this report?')">
      <input type="hidden" name="delete_id" value="<?= e($row['reportID']) ?>">
      <button type="submit" class="btn btn-danger">🗑 Delete Report</button>
    </form>
    <?php else: ?>

  <span class="info-note">
    <?= $row['status'] == 'Completed' 
        ? 'Completed reports cannot be edited or deleted.' 
        : 'Deleted reports from admin cannot be edited.' ?>
  </span>
  <?php endif; ?>

<?php endif; ?>

</div>

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
