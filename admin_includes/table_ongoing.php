<?php
include 'db_connect.php';
$sql = "SELECT * FROM intellectual_properties WHERE status='Ongoing'";

$sql = "
SELECT ip.*, c.campus_name, d.department_name
FROM intellectual_properties ip
LEFT JOIN campuses c ON ip.campus_id = c.campus_id
LEFT JOIN departments d ON ip.department_id = d.department_id
WHERE ip.status='Ongoing'
";

$result = $conn->query($sql);
?>

<style>

#modal_all_files p {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 8px 0;
  background: #f7f9fa;
  padding: 6px 10px;
  border-radius: 6px;
}

#modal_all_files p strong {
  flex: 1;
}

#modal_all_files a.view-btn {
  margin-left: 10px;
}

.modal {
  display: none;
  position: fixed;
  inset: 0;
  z-index: 1000;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
}

.modal.active {
  display: flex;
  opacity: 1;
  visibility: visible;
}

.modal-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(20, 20, 20, 0.55);
  backdrop-filter: blur(8px);
  transition: opacity 0.3s ease;
}

.modal-content {
  position: relative;
  background: linear-gradient(145deg, #ffffff, #f9f9f9);
  border: none;
  border-radius: 12px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.25);
  overflow: hidden;
  width: 100%;
  max-height: 85vh;
  transform: translateY(30px);
  transition: all 0.3s ease-in-out;
}

.modal.active .modal-content {
  transform: translateY(0);
}

.modal-lg {
  max-width: 700px;
}

/* Header */
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #28453D;
  color: #fff;
  padding: 1rem 1.5rem;
  border-bottom: 3px solid #28453D;
}

.modal-header h2 {
  font-size: 1.3rem;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.modal-close {
  background: rgba(255,255,255,0.2);
  border: none;
  border-radius: 8px;
  font-size: 1.3rem;
  color: #fff;
  cursor: pointer;
  transition: 0.2s ease;
}

.modal-close:hover {
  background: rgba(255,255,255,0.35);
}

/* Body */
.modal-body {
  padding: 1.5rem;
  overflow-y: auto;
  max-height: 65vh;
  color: #222;
  line-height: 1.6;
}

.details-grid p {
  margin: 0.3rem 0;
  font-size: 0.95rem;
}

.details-grid strong {
  color: #000000ff;
  font-weight: 600;
}

/* Divider */
.divider {
  border-top: 1px solid #ddd;
  margin: 1.2rem 0;
}

/* Uploaded Files */
.file-section p {
  margin-bottom: 0.5rem;
  font-weight: 600;
}

/* Remarks */
.remarks-box {
  border: 1px solid #ccc;
  border-radius: 8px;
  padding: 12px;
  background-color: #f8f9fa;
}

.remarks-box label {
  font-weight: 600;
  display: block;
  margin-bottom: 6px;
  color: #333;
}

.remarks-box textarea {
  width: 100%;
  resize: vertical;
  border: 1px solid #bbb;
  border-radius: 6px;
  padding: 8px;
  font-size: 0.95rem;
  font-family: inherit;
  transition: border-color 0.2s ease;
}

.remarks-box textarea:focus {
  border-color: #0bb5b5;
  outline: none;
  box-shadow: 0 0 4px rgba(11,181,181,0.4);
}

/* Footer */
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 1rem 1.5rem;
  border-top: 1px solid #ddd;
  background: #fafafa;
}

/* Buttons */
.approve-btns,
.disapprove-btns {
  display: inline-block;
  min-width: 120px;
  padding: 8px 16px;
  font-size: 14px;
  font-weight: 600;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
  text-align: center;
  justify-content: center;
}

.approve-btns {
  background: linear-gradient(135deg, #2ecc70a2, #2ecc70a2);
  color: white;
}

.approve-btns:hover {
  background: linear-gradient(135deg, #27ae60, #219150);
  transform: translateY(-1px);
}

.disapprove-btns {
  background: linear-gradient(135deg, #e74d3cc6, #e74d3cc6);
  color: white;
}

.disapprove-btns:hover {
  background: linear-gradient(135deg, #c0392b, #a93226);
  transform: translateY(-1px);
}

.view-btn {
  display: inline-block;
  background: linear-gradient(135deg, #3498db, #2980b9);
  color: #fff;
  text-decoration: none;
  padding: 6px 14px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  transition: all 0.2s ease-in-out;
  outline: none;
  border: none;
}

.view-btn:hover {
  background: linear-gradient(135deg, #2980b9, #2471a3);
  transform: translateY(-1px);
}

.view-btn:focus,
.view-btn:active {
  outline: none;
  box-shadow: none;
  filter: brightness(100%);
}
</style>

<div class="table-container">
  <!-- Table Header -->
  <div class="table-header">
    <div>Title</div>
    <div>Classification</div>
    <div>Status</div>
    <div>Campus</div>
    <div></div>
  </div>

  <!-- Table Body -->
  <div class="table-body">
    <?php
    if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        // Build files HTML for this row
        $files_html = ""; // reset for each row
        $tracking = htmlspecialchars($row['tracking_id'], ENT_QUOTES);

        if (!empty($row['endorsement_letter'])) {
                $files_html .= "<p>
                  Endorsement Letter: 
                  <span class='file-name'>{$row['endorsement_letter']}</span> 
                  <a href='uploads/{$row['endorsement_letter']}' target='_blank' class='view-btn'>View</a>
                </p>";
              }
              if (!empty($row['application_form'])) {
                  $files_html .= "<p>Application Form: <span class='file-name'>{$row['application_form']}</span><a href='uploads/{$row['application_form']}' target='_blank' class='view-btn'>View</a></p>";
              }
              if (!empty($row['project_file'])) {
                  $files_html .= "<p>Project File: <span class='file-name'>{$row['project_file']}</span> <a href='uploads/{$row['project_file']}' target='_blank' class='view-btn'>View</a></p>";
              }
              if (!empty($row['authors_file'])) {
                  $files_html .= "<p>Authors File: <span class='file-name'>{$row['authors_file']}</span> <a href='uploads/{$row['authors_file']}' target='_blank' class='view-btn'>View</a></p>";
              }
              if (!empty($row['application_fee'])) {
                  $files_html .= "<p>Application Fee: <span class='file-name'>{$row['application_fee']}</span> <a href='uploads/{$row['application_fee']}' target='_blank' class='view-btn'>View</a></p>";
              }
              if (!empty($row['issued_certificate'])) {
                  $files_html .= "<p>Issued Certificate: <span class='file-name'>{$row['issued_certificate']}</span> <a href='uploads/{$row['issued_certificate']}' target='_blank' class='view-btn'>View</a></p>";
              }

            echo "
            <div class='table-row-card'>
                <div data-label='Title'>
                    <strong>{$row['title']}</strong><br>
                    <small class='tracking-id' onclick='copyToClipboard(\"$tracking\", this)'>
                        <ion-icon name=\"copy-outline\" style=\"font-size: 12px;\"></ion-icon>
                        {$row['tracking_id']}
                    </small>
                </div>
                <div data-label='Classification'>{$row['classification']}</div>
                <div data-label='Status'>
                    <span class='status-badge status-ongoing'>{$row['status']}</span>
                </div>
                <div date-label='Campus'>{$row['campus_name']}</div>
                <div data-label='Action'>
                    <!-- Review Button -->
                    <button type='button' class='review-btn' 
                            data-id='{$row['ip_id']}'
                            data-tracking='{$row['tracking_id']}'
                            data-title='{$row['title']}'
                            data-authors='{$row['authors']}'
                            data-email='{$row['email']}'
                            data-classification='{$row['classification']}'
                            data-campus='{$row['campus_name']}'
                            data-department_name='".htmlspecialchars($row['department_name'], ENT_QUOTES)."'
                            data-applicant_name='{$row['applicant_name']}'
                            data-files='".htmlspecialchars($files_html, ENT_QUOTES)."'
                            >
                        <ion-icon name='eye-outline'></ion-icon>
                    </button>
                </div>
            </div>";
        }
    } else {
        echo "<p class='no-data'>No ongoing applications</p>";
    }
    ?>
  </div>
</div>

<!-- 🌟 Custom Review Modal -->
<div id="reviewModal" class="modal">
  <div class="modal-backdrop"></div>
  <div class="modal-content modal-lg">
    <div class="modal-header">
      <h2>Review Application</h2>
      <button type="button" class="modal-close">&times;</button>
    </div>

    <div class="modal-body">
      <form method="POST" action="update_status.php">
        <input type="hidden" name="ip_id" id="modal_ip_id">
        <input type="hidden" name="tab" value="ongoing">

        <!-- Details Grid -->
        <div class="details-grid">
          <p><strong>Tracking ID:</strong> <span id="modal_tracking"></span></p>
          <p><strong>Title:</strong> <span id="modal_title"></span></p>
          <p><strong>Authors:</strong> <span id="modal_authors"></span></p>
          <p><strong>Email:</strong> <span id="modal_email"></span></p>
          <p><strong>Classification:</strong> <span id="modal_classification"></span></p>
          <p><strong>Campus:</strong> <span id="modal_campus"></span></p>
          <p><strong>Department:</strong> <span id="modal_department_name"></span></p>
          <p><strong>Applicant Name:</strong> <span id="modal_applicant_name"></span></p>
        </div>

        <div class="divider"></div>

        <!-- Uploaded Files -->
        <div id="modal_files" class="file-section">
          <p><strong>Uploaded Files:</strong></p>
          <div id="modal_all_files"></div> 
        </div>

        <div class="divider"></div>

        <!-- Remarks Section -->
        <div class="remarks-box">
          <label for="remarks">Remarks:</label>
          <textarea id="remarks" name="remarks" rows="3" placeholder="Enter remarks or feedback here..."></textarea>
        </div>
        
        <div class="modal-footer">
          <button type="submit" name="new_status" value="Completed" class="approve-btns">Approve</button>
          <button type="submit" name="new_status" value="Pending" class="disapprove-btns">For Revision</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const modal = document.getElementById('reviewModal');
  const closeBtn = modal.querySelector('.modal-close');
  const backdrop = modal.querySelector('.modal-backdrop');
  const reviewButtons = document.querySelectorAll('.review-btn');

  // Open modal and fill fields
  reviewButtons.forEach(button => {
    button.addEventListener('click', () => {
      document.getElementById('modal_ip_id').value = button.dataset.id;
      document.getElementById('modal_tracking').textContent = button.dataset.tracking;
      document.getElementById('modal_title').textContent = button.dataset.title;
      document.getElementById('modal_authors').textContent = button.dataset.authors;
      document.getElementById('modal_email').textContent = button.dataset.email;
      document.getElementById('modal_classification').textContent = button.dataset.classification;
      document.getElementById('modal_campus').textContent = button.dataset.campus;
      document.getElementById('modal_department_name').textContent = button.dataset.department_name;
      document.getElementById('modal_applicant_name').textContent = button.dataset.applicant_name;
      
      document.getElementById('modal_all_files').innerHTML = button.dataset.files;



      modal.classList.add('active');
    });
  });

  // Close modal
  closeBtn.addEventListener('click', () => modal.classList.remove('active'));
  backdrop.addEventListener('click', () => modal.classList.remove('active'));
});
</script>
