<?php
require_once __DIR__ . "/session.php";
include "db.php";

// Admins only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=Access denied");
    exit();
}

if (!isset($_GET['id'])) {
    die("Report ID is missing.");
}

$reportID = (int) $_GET['id'];
$role = 'admin';   // the role comes from the login session, never from the URL

$sql = "
    SELECT 
        r.reportID,
        r.residentID,
        r.type,
        r.description,
        r.city,
        r.neighborhood,
        r.street,
        r.building_no,
        r.severity,
        r.status,
        r.image,
        r.created_at,
        u.firstName,
        u.lastName
    FROM report r
    JOIN user u ON r.residentID = u.userID
    WHERE r.reportID = ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $reportID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Report not found.");
}

$row = $result->fetch_assoc();

function formatType($type) {
    if ($type === 'Water') return '💧 Water Issue';
    if ($type === 'Electricity') return '⚡ Electricity Issue';
    return $type;
}

function formatStatus($status) {
    return $status;
}

function statusClass($status) {
    if ($status === 'Pending') return 'pending';
    if ($status === 'In Progress') return 'progress';
    if ($status === 'Completed') return 'completed';
    if ($status === 'Deleted') return 'deleted';
    return '';
}

function severityClass($severity) {
    if ($severity === 'Low') return 'low';
    if ($severity === 'Medium') return 'medium';
    if ($severity === 'High') return 'high';
    return '';
}

function formatSeverity($severity) {
    return $severity;
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
  border: 1px solid #eee;
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
  font-weight: 600;
}

.photo-box {
  margin-top: 15px;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #eee;
  max-width: 500px;
}

.photo-box img {
  width: 100%;
  display: block;
}

.actions {
  display: flex;
  gap: 15px;
  margin-top: 25px;
  flex-wrap: wrap;
}

.back-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #22a06b;
  color: #fff;
  padding: 10px 16px;
  border-radius: 12px;
  font-weight: 600;
  text-decoration: none;
  margin: 10px 0 0 10px;
}

.back-btn:hover {
  background: #1d8b5d;
}

.btn-danger {
  background: white;
  border: 2px solid #e57373;
  color: #d33;
  padding: 12px 18px;
  border-radius: 10px;
  cursor: pointer;
}

.btn-danger:hover {
  background: #fff5f5;
}

.details-top {
  display: flex;
  align-items: center;
  gap: 10px;
}

.details-title h2 {
  margin: 0;
}

.details-title span {
  color: gray;
}

.status-row {
  margin-top: 15px;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.admin-update-form {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  align-items: center;
}

.admin-update-form select {
  padding: 10px 12px;
  border-radius: 10px;
  border: 1px solid #ddd;
}

.info-note {
  font-weight: 600;
  color: #888;
}
</style>
</head>

<body>

<header class="topbar">
  <div class="container topbar-inner">

    <div class="brand">
      <?php if ($role === 'admin') { ?>
        <a href="admin.php"><img src="images/logo.png" alt="Logo"></a>
        <span class="brand-text"><a href="admin.php">Rasheed</a></span>
      <?php } else { ?>
        <a href="main.php"><img src="images/logo.png" alt="Logo"></a>
        <span class="brand-text"><a href="main.php">Rasheed</a></span>
      <?php } ?>
    </div>

    <nav class="nav-links">
      <?php if ($role === 'admin') { ?>
        <a href="logout.php" class="nav-link logout">
          <i class="fa-solid fa-right-from-bracket"></i> Log Out
        </a>
      <?php } else { ?>
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
      <?php } ?>
    </nav>

  </div>
</header>

<div class="container main-content">

  <h1 class="page-title">Report Details</h1>

  <?php if ($role === 'admin') { ?>
    <a href="admin.php" class="back-btn">
      <i class="fa-solid fa-arrow-left"></i> Back
    </a>
  <?php } else { ?>
    <a href="MyReports.php" class="back-btn">
      <i class="fa-solid fa-arrow-left"></i> Back
    </a>
  <?php } ?>

  <div class="details-box">

    <div class="details-top">
      <div class="icon"><?php echo $row['type'] === 'Water' ? '💧' : '⚡'; ?></div>
      <div class="details-title">
        <h2>Report #<?php echo htmlspecialchars($row['reportID']); ?></h2>
        <span><?php echo htmlspecialchars(formatType($row['type'])); ?></span>
      </div>
    </div>

    <div class="status-row">
      <span class="badge" style="background:#eee;">
        <?php echo htmlspecialchars(formatStatus($row['status'])); ?>
      </span>

      <span class="badge <?php echo severityClass($row['severity']); ?>">
        <?php echo htmlspecialchars(formatSeverity($row['severity'])); ?>
      </span>
    </div>

    <div style="margin-top:20px;">
      <p class="label">DESCRIPTION</p>
      <p><?php echo htmlspecialchars($row['description']); ?></p>
    </div>

    <div style="margin-top:20px;">
      <p class="label">REPORTED BY</p>
      <p><?php echo htmlspecialchars($row['firstName'] . " " . $row['lastName']); ?></p>
    </div>

    <div style="margin-top:20px;">
      <p class="label">LOCATION</p>

      <table class="location-table">
        <tr>
          <td class="label">City</td>
          <td><?php echo htmlspecialchars($row['city']); ?></td>
        </tr>
        <tr>
          <td class="label">Neighborhood</td>
          <td><?php echo htmlspecialchars($row['neighborhood']); ?></td>
        </tr>
        <tr>
          <td class="label">Street</td>
          <td><?php echo htmlspecialchars($row['street']); ?></td>
        </tr>
        <tr>
          <td class="label">Building</td>
          <td><?php echo htmlspecialchars($row['building_no']); ?></td>
        </tr>
      </table>
    </div>

    <div style="margin-top:20px;">
      <p class="label">SUBMITTED</p>
      <p><?php echo date("M j, Y - h:i A", strtotime($row['created_at'])); ?></p>
    </div>

    <div style="margin-top:20px;">
      <p class="label">PHOTO</p>

      <?php if (!empty($row['image'])) { ?>
        <div class="photo-box">
          <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Report Photo">
        </div>
      <?php } else { ?>
        <p>No photo uploaded.</p>
      <?php } ?>
    </div>

    <div class="actions">

      <?php if ($role === 'admin') { ?>

        <?php if ($row['status'] !== 'Completed' && $row['status'] !== 'Deleted') { ?>
          <form class="admin-update-form" action="update_status.php" method="POST">
            <input type="hidden" name="reportID" value="<?php echo htmlspecialchars($row['reportID']); ?>">

            <select name="status" required>
              <option value="Pending" <?php if ($row['status'] === 'Pending') echo 'selected'; ?>>Pending</option>
              <option value="In Progress" <?php if ($row['status'] === 'In Progress') echo 'selected'; ?>>In Progress</option>
              <option value="Completed" <?php if ($row['status'] === 'Completed') echo 'selected'; ?>>Completed</option>
              <option value="Deleted" <?php if ($row['status'] === 'Deleted') echo 'selected'; ?>>Deleted</option>
            </select>

            <button type="submit" class="btn">Update Status</button>
          </form>
        <?php } elseif ($row['status'] === 'Completed') { ?>
          <span class="info-note">No actions available. This report is already completed.</span>
        <?php } elseif ($row['status'] === 'Deleted') { ?>
          <span class="info-note">No actions available. This report is already deleted.</span>
        <?php } ?>

      <?php } ?>

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