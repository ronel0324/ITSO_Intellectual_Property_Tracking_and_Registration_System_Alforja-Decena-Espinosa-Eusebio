<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | ITSO System</title>
  <link rel="icon" type="image/png" href="assets/ITSO.png">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="assets/css/login_signup.css">
</head>
<body>
  <div class="wrapper">
    <div class="info-text login">
      <img src="assets/imgs/itsolog.png" alt="ITSO Logo">
    </div>

    <div class="form-box login">
      <h2>Welcome Back</h2>
      <p class="subtitle">Sign in to continue to ITSO System</p>
      <form action="process_login.php" method="POST">
        <div class="input-box">
          <i class='bx bxs-user'></i>
          <input type="text" name="username" autocomplete="off" required>
          <label>Username</label>
        </div>
        <div class="input-box">
          
          <input type="password" id="password" name="password" required>
          <label>Password</label>
          <i class='bx bx-show toggle-password' onclick="togglePassword()"></i>
        </div>
        <button type="submit" class="btn">Login</button>
        <div class="logreg-link">
          <p>Don’t have an account? <a href="register.php">Sign up</a></p>
        </div>
      </form>
    </div>
  </div>

  <script>
    function togglePassword() {
      const password = document.getElementById("password");
      const toggleIcon = document.querySelector(".toggle-password");
      if (password.type === "password") {
        password.type = "text";
        toggleIcon.classList.replace("bx-show", "bx-hide");
      } else {
        password.type = "password";
        toggleIcon.classList.replace("bx-hide", "bx-show");
      }
    }
  </script>
</body>
</html>
