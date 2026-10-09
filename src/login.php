<?php
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - Rasheed</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

  <style>
    * {
      box-sizing: border-box;
      margin: 0;
    }

    body {
      font-family: "Inter", sans-serif;
      background: #f3f4f6;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background: linear-gradient(to bottom, #eef8f2, #e4f4eb);
    }

    .card {
      width: 100%;
      max-width: 420px;
      background: white;
      border-radius: 20px;
      padding: 30px 25px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.05);
      text-align: center;
    }

    .logo {
      margin-bottom: 20px;
    }

    .logo img {
      height: 80px;
    }

    h2 {
      font-size: 22px;
      font-weight: 800;
      margin-bottom: 5px;
    }

    .subtitle {
      font-size: 14px;
      color: #6b7280;
      margin-bottom: 20px;
    }

    .form-group {
      text-align: left;
      margin-bottom: 15px;
    }

    .form-group label {
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 5px;
      display: block;
    }

    .form-group input {
      width: 100%;
      height: 45px;
      padding: 10px;
      border-radius: 10px;
      border: 1px solid #d1d5db;
      font-size: 14px;
    }

    .form-group input:focus {
      outline: none;
      border-color: #22a06b;
      box-shadow: 0 0 0 3px rgba(34,160,107,0.1);
    }

    .btn {
      width: 100%;
      height: 45px;
      background: #22a06b;
      color: white;
      border: none;
      border-radius: 10px;
      font-weight: 700;
      margin-top: 10px;
      cursor: pointer;
    }

    .btn:hover {
      background: #1d8b5d;
    }

    .signup-link {
      margin-top: 15px;
      font-size: 13px;
      color: #6b7280;
    }

    .signup-link a {
      color: #22a06b;
      font-weight: 600;
      text-decoration: none;
    }

    .signup-link a:hover {
      text-decoration: underline;
    }

    .error {
      color: red;
      font-size: 13px;
      margin-top: 10px;
    }
  </style>
</head>

<body>

  <div class="card">

    <div class="logo">
      <img src="images/logo.png" alt="Rasheed Logo">
    </div>

    <h2>Welcome back</h2>
    <p class="subtitle">Log in to your account</p>

    <form id="loginForm" method="POST" action="process_login.php">

      <div class="form-group">
        <label>Phone Number</label>
        <input type="text" id="phone" name="phoneNumber" placeholder="05XXXXXXXX">
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" id="password" name="password" placeholder="Enter your password">
      </div>

      <button class="btn" type="submit">Log In</button>

      <div class="error" id="errorMsg">
        <?php echo htmlspecialchars($error); ?>
      </div>

    </form>

    <div class="signup-link">
      Don't have an account? <a href="register.php">Sign up</a>
    </div>

  </div>

  <script>
    const form = document.getElementById("loginForm");
    const errorMsg = document.getElementById("errorMsg");

    form.addEventListener("submit", function(e) {

      let phone = document.getElementById("phone").value.trim();
      let password = document.getElementById("password").value.trim();

      errorMsg.textContent = "";

      if (phone === "" || password === "") {
        errorMsg.textContent = "Please fill all fields";
        e.preventDefault();
        return;
      }

      if (!phone.startsWith("05") || phone.length !== 10) {
        errorMsg.textContent = "Invalid phone number";
        e.preventDefault();
        return;
      }

    });
  </script>

</body>
</html>
