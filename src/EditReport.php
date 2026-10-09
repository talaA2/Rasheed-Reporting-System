<?php
require_once __DIR__ . "/session.php";
include "db.php";

// Only logged-in residents can edit reports
if (!isset($_SESSION['userID']) || ($_SESSION['role'] ?? '') !== 'resident') {
  header("Location: login.php");
  exit();
}
if (!isset($_GET['id'])) {
  die("No report selected");
}

$id = (int) $_GET['id'];
$residentID = (int) $_SESSION['userID'];

// Load the report and make sure it belongs to this resident and can still be edited
$stmt = $conn->prepare("SELECT * FROM report WHERE reportID = ? AND residentID = ?");
$stmt->bind_param("ii", $id, $residentID);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
  die("Report not found.");
}
if ($row['status'] === 'Completed' || $row['status'] === 'Deleted') {
  die("This report can no longer be edited.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $desc = trim($_POST['description'] ?? '');
  $city = trim($_POST['city'] ?? '');
  $neighborhood = trim($_POST['neighborhood'] ?? '');
  $street = trim($_POST['street'] ?? '');
  $building = trim($_POST['building'] ?? '');
  $severity = $_POST['severity'] ?? '';

  if (!in_array($severity, ['Low', 'Medium', 'High'], true)) {
    die("Invalid severity.");
  }
  if ($desc === '' || $city === '' || $neighborhood === '' || $street === '' || $building === '') {
    die("Please fill in all fields. <a href='EditReport.php?id=$id'>Go back</a>");
  }

  $imageName = $row['image'];
  $newImage = save_report_photo($_FILES['photo'] ?? null);
  if ($newImage === false) {
    die("The photo must be a JPG or PNG image under 5 MB. <a href='EditReport.php?id=$id'>Go back</a>");
  }
  if ($newImage !== null) {
    $imageName = $newImage;
  }

  $update = $conn->prepare("UPDATE report SET description = ?, city = ?, neighborhood = ?, street = ?,
                            building_no = ?, severity = ?, image = ?
                            WHERE reportID = ? AND residentID = ?");
  $update->bind_param("sssssssii", $desc, $city, $neighborhood, $street, $building, $severity, $imageName, $id, $residentID);
  $update->execute();

  header("Location: report-det.php?id=$id&updated=1");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Report - Rasheed</title>

<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

</head>
<header class="topbar">
  <div class="container topbar-inner">

    <div class="brand">
      <a href ="main.php"><img src="images/logo.png" alt="Logo"></a>
      <span class="brand-text"><a href ="main.php">Rasheed</span></a>
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

<body class="edit-report-page">

  <div class="edit-report-wrapper">
    <h1 class="page-title">Edit Report</h1>
    <a href="report-det.php?id=<?= e($row['reportID']) ?>" class="back-btn">
      <i class="fa-solid fa-arrow-left"></i> Back
    </a>

    <div class="edit-report-card">
      <div class="edit-report-header">
        <div class="edit-report-icon">
          <i class="fa-solid fa-droplet"></i>
        </div>

        <div>
          <h1 class="edit-report-id">RPT-<?= e($row['reportID']) ?></h1>
          <p class="edit-report-type"><?= e($row['type']) ?> Issue</p>
        </div>
      </div>

      <form id="editReportForm" method="POST" enctype="multipart/form-data">
        <h3 class="edit-section-title">Edit Description</h3>
        <textarea id="description" name="description"><?= e($row['description']) ?></textarea>

        <h3 class="edit-section-title">Edit Location</h3>
        <div class="edit-location-grid">
          <div class="edit-field">
            <label for="city">City</label>
            <input type="text" id="city" name="city" value="<?= e($row['city']) ?>">
          </div>

          <div class="edit-field">
            <label for="neighborhood">Neighborhood</label>
            <input type="text" id="neighborhood" name="neighborhood" value="<?= e($row['neighborhood']) ?>">
          </div>

          <div class="edit-field">
            <label for="street">Street</label>
            <input type="text" id="street" name="street" value="<?= e($row['street']) ?>">
          </div>

          <div class="edit-field">
            <label for="building">Building</label>
            <input type="text" id="building" name="building" value="<?= e($row['building_no']) ?>">
          </div>
          <input type="hidden" name="severity" id="severityInput" value="<?= e($row['severity']) ?>">
        </div>

        <h3 class="edit-section-title">Severity Level</h3>
        <div class="edit-severity-row">
          <button type="button" class="edit-severity-btn <?= $row['severity']=='Low'?'active':'' ?>">Low</button>
          <button type="button" class="edit-severity-btn <?= $row['severity']=='Medium'?'active':'' ?>">Medium</button>
          <button type="button" class="edit-severity-btn <?= $row['severity']=='High'?'active':'' ?>">High</button>
        </div>

        <h3 class="edit-section-title">Update Photo</h3>
        <div class="edit-upload-box" id="uploadBox">
          <div class="edit-current-photo">
            <img id="currentPhoto" src="<?= empty($row['image']) ? '' : 'uploads/' . e($row['image']) ?>" alt="Report Image"<?= empty($row['image']) ? ' style="display:none"' : '' ?>>
          </div>
          <i class="fa-solid fa-cloud-arrow-up"></i>
          <div class="edit-upload-main" id="uploadMain">Click to upload a new image</div>
          <div class="edit-upload-sub">PNG, JPG up to 5MB</div>
          <input type="file" id="photoInput" name="photo" accept=".png,.jpg,.jpeg" hidden>
        </div>

        <div class="edit-actions">
          <button type="submit" class="save-btn" >
            <i class="fa-solid fa-floppy-disk"></i> Save Changes
          </button>

          <a href="report-det.php?id=<?= e($row['reportID']) ?>" class="cancel-btn">
            Cancel
          </a>
        </div>

        <p class="edit-success-message" id="successMessage"></p>
      </form>
    </div>
  </div>

  <script>
    const uploadBox = document.getElementById("uploadBox");
    const photoInput = document.getElementById("photoInput");
    const uploadMain = document.getElementById("uploadMain");
    const successMessage = document.getElementById("successMessage");

    uploadBox.addEventListener("click", () => {
      photoInput.click();
    });

    const currentPhoto = document.getElementById("currentPhoto");

photoInput.addEventListener("change", () => {
  if (photoInput.files.length > 0) {
    const file = photoInput.files[0];

    uploadMain.textContent = file.name;

    const reader = new FileReader();
    reader.onload = function(e) {
      currentPhoto.src = e.target.result;
      currentPhoto.style.display = "";
    };

    reader.readAsDataURL(file);
  }
});

    const severityButtons = document.querySelectorAll(".edit-severity-btn");
    severityButtons.forEach(btn => {
      btn.addEventListener("click", () => {
        severityButtons.forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
        document.getElementById("severityInput").value = btn.textContent.trim();
      });
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
