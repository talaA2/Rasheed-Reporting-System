<?php
require_once __DIR__ . "/session.php";
include "db.php";

if (!isset($_SESSION['userID'])) {
  header("Location: login.php");
  exit();
}

$residentID = (int) $_SESSION['userID'];

$stmt = $conn->prepare("SELECT * FROM report
                        WHERE residentID = ?
                        AND NOT (status = 'Deleted' AND deletedByUser = 1)
                        ORDER BY reportID DESC");
$stmt->bind_param("i", $residentID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Reports</title>

<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

</head>

<body>

<!-- NAVBAR -->
<header class="topbar">
    <div class="container topbar-inner">
      <div class="brand">
        <a href ="main.php"><img src="images/logo.png" alt="Logo"></a>
      <span class="brand-text"><a href ="main.php">Rasheed</span></a>
      </div>

      <nav class="nav-links">
        <a href="AddReport.php" class="nav-link "><i class="fa-regular fa-file-lines"></i> Add Report</a>
        <a href="MyReports.php" class="nav-link active"><i class="fa-regular fa-clipboard"></i> My Reports</a>
        <a href="Rewards.php" class="nav-link"><i class="fa-regular fa-star"></i> Rewards</a>
        <a href="Notifications.php" class="nav-link"><i class="fa-regular fa-bell"></i> Notifications</a>
        <a href="logout.php" class="nav-link logout"><i class="fa-solid fa-right-from-bracket"></i> Log Out</a>
      </nav>
    </div>
  </header>


<!-- MAIN -->
<div class="container main-content">

  <h1 class="page-title">My Reports</h1>
  <p class="page-subtitle">View and manage your submitted reports.</p>

  <!-- FILTER -->
  <div class="search-row">

<input type="text" id="searchInput" placeholder="Search reports...">

    <select id="typeFilter">
      <option value="all">All Types</option>
      <option value="water">Water</option>
      <option value="electricity">Electricity</option>
    </select>

    <select id="severityFilter">
      <option value="all">All Severity</option>
      <option value="low">Low</option>
      <option value="medium">Medium</option>
      <option value="high">High</option>
    </select>

  </div>

  <?php while($row = $result->fetch_assoc()): ?>

<a href="report-det.php?id=<?= e($row['reportID']) ?>"
   class="report-card"
   data-type="<?= e(strtolower($row['type'])) ?>"
   data-severity="<?= e(strtolower($row['severity'])) ?>">

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

<?php endwhile; ?>
<p id="noResults" style="display:none; text-align:center; margin-top:20px; color:gray;">
  No reports found
</p>
  </div>
 
<!-- JS -->
<script>

const typeFilter = document.getElementById("typeFilter");
const severityFilter = document.getElementById("severityFilter");
const searchInput = document.getElementById("searchInput");
const noResults = document.getElementById("noResults");
const cards = document.querySelectorAll(".report-card");

function applyFilters() {
  const type = typeFilter.value;
  const severity = severityFilter.value;
  const search = searchInput.value.trim().toLowerCase();

  let found = false;

  cards.forEach(card => {

    const cardType = card.getAttribute("data-type");
    const cardSeverity = card.getAttribute("data-severity");
    const text = card.innerText.toLowerCase();

    let show = true;

    if (type !== "all" && cardType !== type) show = false;
    if (severity !== "all" && cardSeverity !== severity) show = false;
    if (search && !text.includes(search)) show = false;

    if (show) {
      card.style.display = "flex";
      found = true;
    } else {
      card.style.display = "none";
    }

  });

  noResults.style.display = found ? "none" : "block";
}

typeFilter.addEventListener("change", applyFilters);
severityFilter.addEventListener("change", applyFilters);
searchInput.addEventListener("input", applyFilters);

</script>
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
