<?php
include 'db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get all campuses
$campusQuery = "SELECT campus_id, campus_name FROM campuses";
$campusResult = $conn->query($campusQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>New IP Application</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">


<div class="w-full p-6">

  <!-- Header -->
  <div class="flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-4">
    <a href="index.php" class="border border-gray-300 px-4 py-2 rounded-md hover:bg-gray-200 transition text-sm md:text-base">
      ← Back to Dashboard
    </a>
    <div>
      <h1 class="text-xl md:text-2xl font-bold">New IP Application</h1>
      <p class="text-gray-500 text-sm md:text-base">Submit a new intellectual property application</p>
    </div>
  </div>

  <!-- Progress -->
<div class="bg-white rounded-lg shadow p-4 md:p-6 border overflow-hidden w-full">
    <div class="space-y-3 md:space-y-4 mb-4 md:mb-6">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-1 sm:gap-0">
        <h3 class="font-medium text-sm md:text-base">
          Step <span id="current-step">1</span> of 4: <span id="step-title">Basic Information</span>
        </h3>
        <span class="text-gray-500 text-xs md:text-sm">
          <span id="progress-percent">25</span>% Complete
        </span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2">
        <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all" style="width: 25%"></div>
      </div>
    </div>
  </div>

  <!-- FORM START -->
  <form id="application-form-steps" action="add_entry1.php" method="POST" enctype="multipart/form-data" class="space-y-4">

    <!-- Step 1 -->
<div id="step-1" class="space-y-4 w-full"> 
      <div>
        <label class="block text-sm md:text-base font-medium mb-1">Title *</label>
        <input type="text" name="title" class="w-full border rounded px-3 py-2" required>
      </div>
      
        <div id="authors-section">
    <label class="block text-sm md:text-base font-medium mb-1">Authors *</label>
    
    <!-- main author -->
    <div class="flex items-center gap-2 mb-2">
      <input type="text" name="authors[]" class="w-full border rounded px-3 py-2" placeholder="Enter author name" required>
      <!-- add button -->
      <button type="button" id="add-author-btn" 
              class="bg-green-500 text-white px-3 py-2 rounded hover:bg-green-600 text-sm">
        +
      </button>
    </div>
  </div>

  <div>
    <label class="block text-sm md:text-base font-medium mb-1">Author Email *</label>
    <input type="email" name="email" class="w-full border rounded px-3 py-2" required>
  </div>

      <div>
        <label class="block text-sm md:text-base font-medium mb-1">Classification *</label>
        <select name="classification" class="w-full border rounded px-3 py-2" required>
          <option value="">--Select--</option>
          <option value="Copyright">Copyright</option>
          <option value="Patent">Patent</option>
          <option value="Trademark">Trademark</option>
          <option value="Utility Model">Utility Model</option>
          <option value="Industrial Design">Industrial Design</option>
        </select>
      </div>
</div>

    <!-- Step 2 -->
  <div id="step-2" class="space-y-4 hidden">
    <div>
      <label class="block text-sm md:text-base font-medium mb-1">Applicant Name *</label>
      <input type="text" name="applicant_name" class="w-full border rounded px-3 py-2" required>
    </div>

      <div>
        <label class="block text-sm md:text-base font-medium mb-1">Campus *</label>
        <select name="campus_id" class="w-full border rounded px-3 py-2" required>
          <option value="">-- Select Campus --</option>
          <?php while($row = $campusResult->fetch_assoc()): ?>
            <option value="<?= $row['campus_id']; ?>"><?= htmlspecialchars($row['campus_name']); ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <div>
        <label class="block text-sm md:text-base font-medium mb-1">Department *</label>
        <select name="department_id" id="departmentSelect" class="w-full border rounded px-3 py-2" required>
          <option value="">--Select Department--</option>
        </select>
      </div>
</div>

    <!-- Step 3 -->
    <div id="step-3" class="space-y-4 hidden">
      <div>
        <label class="block text-sm md:text-base font-medium mb-1">Date Submitted to ITSO *</label>
        <input type="date" name="date_submitted_to_itso" class="w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <label class="block text-sm md:text-base font-medium mb-1">Date Submitted to IPOPHIL</label>
        <input type="date" name="date_submitted_to_ipophil" class="w-full border rounded px-3 py-2">
      </div>
      <div class="flex items-center space-x-2">
        <input type="checkbox" name="submitted_to_ipophil" value="1">
        <label class="text-sm md:text-base">Submitted to IPOPHIL</label>
      </div>
      <input type="hidden" name="status" value="Pending">
    </div>

    <!-- Step 4 -->
    <div id="step-4" class="space-y-4 hidden">
      <h3 class="font-medium text-sm md:text-base">Upload Files </h3>
      <p class="text-gray-500 text-xs md:text-sm">*Only PDF Files. Max size per file: 10MB.</p>
      <label class="block text-sm md:text-base font-medium mb-1">Application Form</label>
      <input type="file" name="application_form" class="w-full border rounded px-3 py-2" required>
      <label class="block text-sm md:text-base font-medium mb-1">Project File</label>
      <input type="file" name="project_file" class="w-full border rounded px-3 py-2" required>
      <label class="block text-sm md:text-base font-medium mb-1">Endorsement Letter</label>
      <input type="file" name="endorsement_letter" class="w-full border rounded px-3 py-2" required>
      <label class="block text-sm md:text-base font-medium mb-1"> Application Fee <p class="text-grat-500 text-xs md:text-sm"><i>*reciept from ipophil</i></label>
      <input type="file" name="application_fee" class="w-full border rounded px-3 py-2">
      <label class="block text-sm md:text-base font-medium mb-1">Issued Certificate <p class="text-grat-500 text-xs md:text-sm"><i>*certificate from ipophil if you already have (optional)</i></label>
      <input type="file" name="issued_certificate" class="w-full border rounded px-3 py-2">
    </div>

    <!-- Navigation -->
    <div class="flex flex-col sm:flex-row justify-between mt-6 gap-2 sm:gap-0">
      <button type="button" id="prev-btn" onclick="prevStep()" class="border px-4 py-2 rounded hover:bg-gray-200 disabled:opacity-50" disabled>
        ← Previous
      </button>
      <button type="button" id="next-btn" onclick="nextStep()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Next →
      </button>
      <button type="submit" id="submit-btn" class="hidden bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
        Submit Application
      </button>
    </div>

  </form>
</div>

<script>
let currentStep = 1;
const totalSteps = 4;

function updateStep() {
  document.querySelectorAll('[id^="step-"]').forEach(el => el.classList.add('hidden'));
  document.getElementById(`step-${currentStep}`).classList.remove('hidden');
  document.getElementById("current-step").innerText = currentStep;
  document.getElementById("progress-percent").innerText = Math.round((currentStep / totalSteps) * 100);
  document.getElementById("progress-bar").style.width = ((currentStep / totalSteps) * 100) + "%";

  document.getElementById("prev-btn").disabled = currentStep === 1;
  document.getElementById("next-btn").classList.toggle("hidden", currentStep === totalSteps);
  document.getElementById("submit-btn").classList.toggle("hidden", currentStep !== totalSteps);
}

function nextStep() {
  const currentStepEl = document.getElementById(`step-${currentStep}`);
  const inputs = currentStepEl.querySelectorAll("input, select, textarea");

  let isValid = true;

  inputs.forEach(input => {
    if (!input.checkValidity()) {
      isValid = false;
      input.reportValidity();
    }
  });

  if (isValid && currentStep < totalSteps) {
    currentStep++;
    updateStep();
  }
}

function prevStep() {
  if (currentStep > 1) {
    currentStep--;
    updateStep();
  }
}

updateStep();

// ===== ADD CO-AUTHORS FUNCTION =====
document.addEventListener("DOMContentLoaded", () => {
  const addBtn = document.getElementById("add-author-btn");
  const authorsSection = document.getElementById("authors-section");

  addBtn.addEventListener("click", () => {
    const newAuthorDiv = document.createElement("div");
    newAuthorDiv.classList.add("flex", "items-center", "gap-2", "mb-2");

    newAuthorDiv.innerHTML = `
      <input type="text" name="authors[]" class="w-full border rounded px-3 py-2" placeholder="Enter co-author name" required>
      <button type="button" class="remove-author bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600 text-sm">–</button>
    `;

    authorsSection.appendChild(newAuthorDiv);

    // Add remove functionality
    newAuthorDiv.querySelector(".remove-author").addEventListener("click", () => {
      newAuthorDiv.remove();
    });
  });
});

const campusSelect = document.querySelector('select[name="campus_id"]');
const departmentSelect = document.getElementById('departmentSelect');

campusSelect.addEventListener('change', () => {
    const campusId = campusSelect.value;

    if (!campusId) {
        departmentSelect.innerHTML = '<option value="">--Select Department--</option>';
        return;
    }

    departmentSelect.innerHTML = '<option value="">Loading...</option>';

    fetch(`fetch_departments.php?campus_id=${campusId}`)
        .then(res => res.json())
        .then(data => {
            departmentSelect.innerHTML = '<option value="">--Select Department--</option>';
            data.forEach(dept => {
                const opt = document.createElement('option');
                opt.value = dept.department_id;
                opt.textContent = dept.department_name;
                departmentSelect.appendChild(opt);
            });
        })
        .catch(err => {
            console.error(err);
            departmentSelect.innerHTML = '<option value="">--Select Department--</option>';
        });
});
</script>

</body>
</html>
