<?php
require_once __DIR__ . "/session.php";
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=Access denied");
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : "all";

$statsSql = "
    SELECT 
        COUNT(*) AS total_reports,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending_reports,
        SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) AS progress_reports,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed_reports
    FROM report
";

$statsResult = $conn->query($statsSql);

if (!$statsResult) {
    die("Stats SQL Error: " . $conn->error);
}

$stats = $statsResult->fetch_assoc();

$total = $stats['total_reports'] ?? 0;
$pending = $stats['pending_reports'] ?? 0;
$progress = $stats['progress_reports'] ?? 0;
$completed = $stats['completed_reports'] ?? 0;

$sql = "
    SELECT 
        r.reportID,
        r.description,
        r.type,
        r.severity,
        r.status,
        r.city,
        r.neighborhood,
        r.street,
        r.building_no,
        r.image,
        r.created_at,
        u.firstName,
        u.lastName
    FROM report r
    JOIN user u ON r.residentID = u.userID
    WHERE 1
";

$params = [];
$types = "";

if ($filter !== "all") {
    $sql .= " AND r.status = ?";
    $params[] = $filter;
    $types .= "s";
}

if ($search !== "") {
    $sql .= " AND (
        r.reportID LIKE ? OR
        r.type LIKE ? OR
        r.severity LIKE ? OR
        r.status LIKE ? OR
        r.description LIKE ? OR
        r.city LIKE ? OR
        r.neighborhood LIKE ? OR
        r.street LIKE ? OR
        u.firstName LIKE ? OR
        u.lastName LIKE ?
    )";

    $searchLike = "%" . $search . "%";

    for ($i = 0; $i < 10; $i++) {
        $params[] = $searchLike;
        $types .= "s";
    }
}

$sql .= " ORDER BY r.created_at DESC, r.reportID DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

/* Functions */
function formatStatus($status) {
    return $status;
}

function statusClass($status) {
    if ($status === "Pending") return "pending";
    if ($status === "In Progress") return "progress";
    if ($status === "Completed") return "completed";
    if ($status === "Deleted") return "deleted";
    return "";
}

function formatType($type) {
    if ($type === "Water") return "💧 Water";
    if ($type === "Electricity") return "⚡ Electricity";
    return ucfirst($type);
}

function formatSeverity($severity) {
    return ucfirst($severity);
}

function severityClass($severity) {
    if ($severity === "Low") return "low";
    if ($severity === "Medium") return "medium";
    if ($severity === "High") return "high";
    return "";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>

<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
.search-box form {
  display: flex;
  gap: 12px;
  width: 100%;
  flex-wrap: wrap;
}

.search-box input {
  flex: 1;
  min-width: 250px;
}

.search-box button {
  padding: 10px 16px;
  border: none;
  border-radius: 10px;
  background: #14532d;
  color: white;
  cursor: pointer;
  font-weight: 600;
}

.location-text {
  font-size: 14px;
  line-height: 1.5;
}

.message-box,
.error-box {
  margin: 20px 0 24px 0;
  padding: 14px 20px;
  border-radius: 14px;
  font-size: 16px;
  line-height: 1.5;
  display: block;
}

.message-box {
  background: #ecfdf3;
  border: 1px solid #4ade80;
  color: #166534;
}

.error-box {
  background: #fff5f5;
  border: 1px solid #ff6b6b;
  color: #b42318;
}

.details-link {
  color: inherit;
  text-decoration: none;
  font-weight: 700;
}

.details-link:hover {
  text-decoration: underline;
}

.empty-row td {
  text-align: center;
  padding: 20px;
  font-weight: 600;
  color: #666;
}
</style>
</head>

<body>

<header class="topbar">
  <div class="container topbar-inner">
    <div class="brand">
      <img src="images/logo.png" alt="Logo">
      <span class="brand-text">Rasheed Admin Panel</span>
    </div>

    <nav class="nav-links">
      <a href="logout.php" class="nav-link logout">
        <i class="fa-solid fa-right-from-bracket"></i> Log Out
      </a>
    </nav>
  </div>
</header>

<div class="hero">
  <div class="container hero-content">
    <h1>Welcome back, Admin 👋</h1>
    <p>Here’s an overview of all reports and system activity.</p>
  </div>
  <div class="hero-curve"></div>
</div>

<div class="container main-content">

  <?php if (isset($_GET['success'])) { ?>
    <div class="message-box">
      <span><?php echo htmlspecialchars($_GET['success']); ?></span>
    </div>
  <?php } ?>

  <?php if (isset($_GET['error'])) { ?>
    <div class="error-box">
      <span><?php echo htmlspecialchars($_GET['error']); ?></span>
    </div>
  <?php } ?>

  <div class="stats">
    <div class="card stat-card">
      <div>
        <p>Total</p>
        <h2><?php echo $total; ?></h2>
      </div>
    </div>

    <div class="card stat-card">
      <div>
        <p>Pending</p>
        <h2><?php echo $pending; ?></h2>
      </div>
    </div>

    <div class="card stat-card">
      <div>
        <p>In Progress</p>
        <h2><?php echo $progress; ?></h2>
      </div>
    </div>

    <div class="card stat-card">
      <div>
        <p>Completed</p>
        <h2><?php echo $completed; ?></h2>
      </div>
    </div>
  </div>

  <div class="search-box">
    <form method="GET" action="admin.php">
      <input 
        type="text" 
        name="search" 
        placeholder="Search reports..." 
        value="<?php echo htmlspecialchars($search); ?>"
      >

      <select name="filter">
        <option value="all" <?php if ($filter === "all") echo "selected"; ?>>All Status</option>
        <option value="Pending" <?php if ($filter === "Pending") echo "selected"; ?>>Pending</option>
        <option value="In Progress" <?php if ($filter === "In Progress") echo "selected"; ?>>In Progress</option>
        <option value="Completed" <?php if ($filter === "Completed") echo "selected"; ?>>Completed</option>
        <option value="Deleted" <?php if ($filter === "Deleted") echo "selected"; ?>>Deleted</option>
      </select>

      <button type="submit">Apply</button>
    </form>
  </div>

  <table id="table">
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Type</th>
      <th>Severity</th>
      <th>Location</th>
      <th>Date</th>
      <th>Status</th>
    </tr>

    <?php if ($result->num_rows > 0) { ?>
      <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
          <td>
            <a class="details-link" href="report_details_admin.php?id=<?php echo urlencode($row['reportID']); ?>">
              <?php echo htmlspecialchars($row['reportID']); ?>
            </a>
          </td>

          <td>
            <?php echo htmlspecialchars($row['firstName'] . " " . $row['lastName']); ?>
          </td>

          <td>
            <?php echo formatType($row['type']); ?>
          </td>

          <td>
            <span class="badge <?php echo severityClass($row['severity']); ?>">
              <?php echo htmlspecialchars(formatSeverity($row['severity'])); ?>
            </span>
          </td>

          <td class="location-text">
            <?php
              echo htmlspecialchars($row['city']) . "<br>";
              echo htmlspecialchars($row['neighborhood']) . "<br>";
              echo htmlspecialchars($row['street']) . "<br>";
              echo "Building " . htmlspecialchars($row['building_no']);
            ?>
          </td>

          <td>
            <?php
              if (!empty($row['created_at'])) {
                  echo date("M j, Y", strtotime($row['created_at']));
              } else {
                  echo "-";
              }
            ?>
          </td>

          <td>
            <span class="status-text <?php echo statusClass($row['status']); ?>">
              <?php echo htmlspecialchars(formatStatus($row['status'])); ?>
            </span>
          </td>
        </tr>
      <?php } ?>
    <?php } else { ?>
      <tr class="empty-row">
        <td colspan="7">No reports found.</td>
      </tr>
    <?php } ?>
  </table>

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