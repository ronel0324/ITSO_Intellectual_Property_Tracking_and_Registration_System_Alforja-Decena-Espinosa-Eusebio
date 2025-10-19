<?php
include 'db_connect.php';
$campusQuery = "SELECT campus_id, campus_name FROM campuses";
$campusResult = $conn->query($campusQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | ITSO System</title>
  <link rel="icon" type="image/png" href="ITSO.png">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="assets/css/login_signup.css">
</head>
<body>
  <div class="wrapper">
    <div class="info-text register">
      <img src="assets/imgs/itsolog.png" alt="ITSO Logo">
    </div>

    <div class="form-box register">
      <h2>Create Account</h2>
      <p class="subtitle">Sign up to get started</p>
      <form action="process_register.php" method="POST">
        <div class="input-box">
          <i class='bx bxs-user'></i>
          <input type="text" name="username" autocomplete="off" required>
          <label>Username</label>
        </div>

        <div class="input-box">
          <i class='bx bxs-lock-alt'></i>
          <input type="password" name="password" required>
          <label>Password</label>
        </div>

        <!-- ROLE -->
        <div class="input-box select-box">
          <select name="role" id="role" required>
            <option value="">Select Role</option>
            <option value="Director">Director</option>
            <option value="Chairperson">Chairperson</option>
            <option value="Coordinator">Coordinator</option>
          </select>
        </div>

        <!-- CAMPUS -->
        <div class="input-box select-box" id="campusBox">
          <select name="campus" id="campus" required>
            <option value="">Select Campus</option>
            <?php while ($campus = $campusResult->fetch_assoc()): ?>
              <option value="<?= htmlspecialchars($campus['campus_id']); ?>">
                <?= htmlspecialchars($campus['campus_name']); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <!-- DEPARTMENT -->
        <div class="input-box select-box" id="departmentBox">
          <select name="department_id" id="department_id">
            <option value="">Select Department</option>
          </select>
        </div>


        <button type="submit" class="btn">Sign Up</button>

        <p class="error-message">
          <?php if (isset($_GET['error'])) echo $_GET['error']; ?>
        </p>

        <div class="logreg-link">
          <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
      </form>
    </div>
  </div>

  <!-- JS CONDITION HANDLER -->
  <script>
    const role = document.getElementById('role');
    const campus = document.getElementById('campus');
    const department_id = document.getElementById('department_id');
    const departmentBox = document.getElementById('departmentBox');

    function updateFieldVisibility() {
      const value = role.value;

      if (value === 'Chairperson' || value === 'Director') {
        // Campus required, department hidden
        departmentBox.style.display = 'none';
        campus.setAttribute('required', 'required');
        department_id.removeAttribute('required');
      } else if (value === 'Coordinator') {
        // Both required
        departmentBox.style.display = 'block';
        campus.setAttribute('required', 'required');
        department_id.setAttribute('required', 'required');
      } else {
        // Default (show both, not required yet)
        departmentBox.style.display = 'block';
        campus.removeAttribute('required');
        department_id.removeAttribute('required');
      }
    }

    role.addEventListener('change', updateFieldVisibility);
    document.addEventListener('DOMContentLoaded', updateFieldVisibility);

    const campusSelect = document.getElementById('campus');
const departmentSelect = document.getElementById('department_id');

campusSelect.addEventListener('change', () => {
    const campusId = campusSelect.value;
    departmentSelect.innerHTML = '<option value="">Loading...</option>';

    fetch(`fetch_departments.php?campus_id=${campusId}`)
        .then(res => res.json())
        .then(data => {
            departmentSelect.innerHTML = '<option value="">Select Department</option>';
            data.forEach(dept => {
                const opt = document.createElement('option');
                opt.value = dept.department_id;
                opt.textContent = dept.department_name;
                departmentSelect.appendChild(opt);
            });
        })
        .catch(err => {
            console.error(err);
            departmentSelect.innerHTML = '<option value="">Select Department</option>';
        });
});
  </script>

</body>
</html>
