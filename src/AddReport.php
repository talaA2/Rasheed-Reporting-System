<?php
require_once __DIR__ . "/session.php";
include "db.php";
if (!isset($_SESSION['userID']) || ($_SESSION['role'] ?? '') !== 'resident') {
  header("Location: login.php");
  exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $residentID = $_SESSION['userID']; 

  $desc = trim($_POST['description'] ?? '');
  $issueType = $_POST['issueType'] ?? '';
  $severity = $_POST['severity'] ?? '';
  $city = trim($_POST['city'] ?? '');
  $neighborhood = trim($_POST['neighborhood'] ?? '');
  $street = trim($_POST['street'] ?? '');
  $buildingNo = trim($_POST['buildingNo'] ?? '');

  // Only accept the values the form offers
  if (!in_array($issueType, ['Water', 'Electricity'], true) || !in_array($severity, ['Low', 'Medium', 'High'], true)) {
    die("Invalid report type or severity.");
  }

  $imageName = save_report_photo($_FILES['photo'] ?? null);
  if ($imageName === false) {
    die("The photo must be a JPG or PNG image under 5 MB. <a href='AddReport.php'>Go back</a>");
  }
  $imageName = $imageName ?? "";

  $stmt = $conn->prepare("INSERT INTO report (description, type, severity, city, neighborhood, street, building_no, status, image, residentID)
                          VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?)");
  $stmt->bind_param("ssssssssi", $desc, $issueType, $severity, $city, $neighborhood, $street, $buildingNo, $imageName, $residentID);
  $stmt->execute();

// Get the report ID that was just inserted
$reportID = $conn->insert_id;

// Create notification for submitted report
$message = "Your $issueType report has been submitted and is now Pending.";
$type = "Pending";

$notifStmt = $conn->prepare("INSERT INTO notification (message, type, reportID, residentID) VALUES (?, ?, ?, ?)");
$notifStmt->bind_param("ssii", $message, $type, $reportID, $residentID);
$notifStmt->execute();

  header("Location: MyReports.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Report - Rasheed</title>

  <link rel="stylesheet" href="style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

</head>
<body class="add-page">

  <header class="topbar">
    <div class="container topbar-inner">
      <div class="brand">
        <a href="main.php">
  <img src="images/logo.png" alt="Rasheed Logo">
</a>
        <a href="main.php" class="brand-text">Rasheed</a>
      </div>

      <nav class="nav-links">
        <a href="AddReport.php" class="nav-link active"><i class="fa-regular fa-file-lines"></i> Add Report</a>
        <a href="MyReports.php" class="nav-link"><i class="fa-regular fa-clipboard"></i> My Reports</a>
        <a href="Rewards.php" class="nav-link"><i class="fa-regular fa-star"></i> Rewards</a>
        <a href="Notifications.php" class="nav-link"><i class="fa-regular fa-bell"></i> Notifications</a>
        <a href="logout.php" class="nav-link logout"><i class="fa-solid fa-right-from-bracket"></i> Log Out</a>
      </nav>
    </div>
  </header>

  <div class="container">

    <h1 class="page-title">Add a Report</h1>
    <p class="helper-text">Help us identify utility issues in your area.</p>

    <form class="form-card" id="reportForm" method="POST" enctype="multipart/form-data">
      <h3 class="section-title">Issue Type</h3>

      <div class="issue-types">
        <button type="button" class="issue-card" data-type="Water">
         <i class="fa-solid fa-droplet"></i>
          <div class="issue-name">Water</div>
          <div class="issue-desc">Water leak or issue</div>
        </button>

        <button type="button" class="issue-card active" data-type="Electricity">
          <i class="fa-solid fa-bolt"></i>
          <div class="issue-name">Electricity</div>
          <div class="issue-desc">Power outage or issue</div>
        </button>
      </div>

      <input type="hidden" id="issueType" name="issueType" value="Electricity">

      <h3 class="section-title">Description</h3>
      <textarea id="description" name="description" placeholder="Describe the issue in detail (min 20 characters)..."></textarea>
      <div class="counter" id="counter">0/20 characters minimum</div>

      <div class="location-grid">
        <div class="field">
          <label for="neighborhood">Neighborhood</label>
          <input type="text" id="neighborhood" name="neighborhood" placeholder="e.g. Al Olaya">
        </div>

        <div class="field">
          <label for="streetName">Street Name</label>
          <input type="text" id="streetName" name="street" placeholder="e.g. King Fahd Rd">
        </div>

        <div class="field">
          <label for="City">City</label>
          <input type="text" id="City" name="city" placeholder="e.g. Riyadh">
        </div>

        <div class="field">
          <label for="buildingNo">Building No.</label>
          <input type="text" id="buildingNo"  name="buildingNo" placeholder="e.g. 42">
        </div>
      </div>

      <h3 class="section-title">Severity Level</h3>
      <div class="severity-row">
        <button type="button" class="severity-btn" data-severity="Low">Low</button>
        <button type="button" class="severity-btn active" data-severity="Medium">Medium</button>
        <button type="button" class="severity-btn" data-severity="High">High</button>
      </div>

      <input type="hidden" id="severityLevel" name="severity" value="Medium">

      <h3 class="section-title">Photo (optional)</h3>
      <div class="upload-box" id="uploadBox">
        <i class="fa-solid fa-cloud-arrow-up"></i>
        <div class="upload-main" id="uploadMain">Click to upload or drag & drop</div>
        <div class="upload-sub">PNG, JPG up to 5MB</div>
        <input type="file" id="photoInput" name="photo" accept=".png,.jpg,.jpeg" hidden>
      </div>

      <button type="submit" class="submit-btn">Submit Report</button>
      <div class="success-message" id="successMessage"></div>
    </form>
  </div>

  <script>
    const issueCards = document.querySelectorAll(".issue-card");
    const issueTypeInput = document.getElementById("issueType");

    issueCards.forEach(card => {
      card.addEventListener("click", () => {
        issueCards.forEach(c => c.classList.remove("active"));
        card.classList.add("active");
        issueTypeInput.value = card.dataset.type;
      });
    });

    const severityButtons = document.querySelectorAll(".severity-btn");
    const severityInput = document.getElementById("severityLevel");

    severityButtons.forEach(btn => {
      btn.addEventListener("click", () => {
        severityButtons.forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
        severityInput.value = btn.dataset.severity;
      });
    });

    const description = document.getElementById("description");
    const counter = document.getElementById("counter");

    description.addEventListener("input", () => {
      counter.textContent = `${description.value.length}/20 characters minimum`;
    });

    const uploadBox = document.getElementById("uploadBox");
    const photoInput = document.getElementById("photoInput");
    const uploadMain = document.getElementById("uploadMain");

    uploadBox.addEventListener("click", () => {
      photoInput.click();
    });

    photoInput.addEventListener("change", () => {
      if (photoInput.files.length > 0) {
        uploadMain.textContent = photoInput.files[0].name;
      }
    });

    const form = document.getElementById("reportForm");
    const successMessage = document.getElementById("successMessage");

    function resetCustomSelections() {
      issueCards.forEach(c => c.classList.remove("active"));
      document.querySelector('.issue-card[data-type="Electricity"]').classList.add("active");
      issueTypeInput.value = "Electricity";

      severityButtons.forEach(b => b.classList.remove("active"));
      document.querySelector('.severity-btn[data-severity="Medium"]').classList.add("active");
      severityInput.value = "Medium";

      uploadMain.textContent = "Click to upload or drag & drop";
      counter.textContent = "0/20 characters minimum";
    }

form.addEventListener("submit", (e) => {
  //e.preventDefault();

  const desc = description.value.trim();
  const neighborhood = document.getElementById("neighborhood").value.trim();
  const streetName = document.getElementById("streetName").value.trim();
  const city = document.getElementById("City").value.trim();
  const buildingNo = document.getElementById("buildingNo").value.trim();

  if (desc.length < 20) {
    alert("Description must be at least 20 characters.");
    e.preventDefault();
    return;
  }

 if (!neighborhood || !streetName || !city || !buildingNo) {
  alert("Please fill in all location fields.");
  e.preventDefault();
  return;
}

});
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
