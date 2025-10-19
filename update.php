<?php
include 'db_connect.php';

if (isset($_GET['ip_id'])) {
    $ip_id = $_GET['ip_id'];

    if (isset($_POST['update'])) {
        $title = $_POST['title'];
        $authors = $_POST['authors'];
        $classification = $_POST['classification'];
        $status = $_POST['status'];
        $submitted = isset($_POST['submitted']) ? 1 : 0;  
        $date_submitted_to_itso = $_POST['date_submitted_to_itso'];
        $date_submitted_to_ipophil = $_POST['date_submitted_to_ipophil'];  
        $department_id = $_POST['department_id'];

        var_dump($_POST); 

        $query = "UPDATE intellectual_properties SET 
            title = '$title',
            authors = '$authors',
            classification = '$classification',
            status = '$status',
            submitted = '$submitted',  // Will be 1 or 0 based on checkbox
            date_submitted_to_itso = '$date_submitted_to_itso',
            date_submitted_to_ipophil = '$date_submitted_to_ipophil',
            department_id = '$department_id'
            WHERE ip_id = $ip_id";

        echo $query;
        exit(); 

        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Record updated successfully!'); window.location.href='dashboard.php';</script>";
        } else {
            echo "Error updating record: " . mysqli_error($conn);
        }
    }

    $result = mysqli_query($conn, "SELECT * FROM intellectual_properties WHERE ip_id = $ip_id");
    $data = mysqli_fetch_assoc($result);
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Intellectual Property</title>
    <link rel="stylesheet" href="assets/css/create_application.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

</head>
<body>
<br><br>
<div class="main-wrapper">
    <div class="container-fluid mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">
                <div class="card shadow-lg p-4 rounded-4">
                    <form method="POST" action="update_ip_process.php?ip_id=<?= $ip_id ?>" enctype="multipart/form-data">
                        <div style="position: relative; top: -10px; right: -560px;">
                            <a href="admin_dashboard.php?page=dashboard_content" class="btn btn-light border-0" style="font-size: 2rem; line-height: 1;">
                                &times;
                            </a>
                        </div>
                        <h2>Update Intellectual Property</h2>
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Title:</label>
                                <input type="text" name="title" class="form-control" value="<?= $data['title'] ?>" required><br>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <label class="form-label">Status:</label>
                                <select name="status" class="form-select">
                                    <option <?= $data['status'] == 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
                                    <option <?= $data['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option <?= $data['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                </select><br>
                            </div>

                            <div class="col-md-6">
                                <label for="classification" class="form-label">Classification:</label>
                                <select name="classification" id="classification" class="form-select" required>
                                <option value="Copyright" <?= $data['classification'] == 'Copyright' ? 'selected' : '' ?>>Copyright</option>
                                <option value="Patent" <?= $data['classification'] == 'Patent' ? 'selected' : '' ?>>Patent</option>
                                <option value="Trademark" <?= $data['classification'] == 'Trademark' ? 'selected' : '' ?>>Trademark</option>
                                <option value="Utility Model" <?= $data['classification'] == 'Utility Model' ? 'selected' : '' ?>>Utility Model</option>
                                <option value="Industrial Design" <?= $data['classification'] == 'Industrial Design' ? 'selected' : '' ?>>Industrial Design</option>
                            </select>

                            </div>

                            <div class="col-md-12">
                                <div class="form-check"> <br>
                                    <label>Submitted to IPOPHIL:</label>
                                    <input type="checkbox" name="submitted" class="form-check-input" <?= $data['submitted'] == 1 ? 'checked' : '' ?>> <br>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Date Submitted to IPOPHIL:</label>
                                <input type="date" name="date_submitted_to_ipophil" class="form-control" value="<?= $data['date_submitted_to_ipophil'] ?>"><br>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Endorsement Letter:</label>
                                <input type="file" name="endorsement_letter" class="form-control"><br>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Application Form:</label>
                                <input type="file" name="application_form" class="form-control"><br>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Application Fee:</label>
                                <input type="file" name="application_fee" class="form-control"><br>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Issued Certificate:</label>
                                <input type="file" name="issued_certificate" class="form-control"><br>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Project File:</label>
                                <input type="file" name="project_file" class="form-control"><br>
                            </div>

                            <div class="col-12 text-center mt-3">
                                <button type="submit" name="update" class="btn btn-primary btn-lg">
                                    <i class="bi bi-save"></i> Update Entry
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelector("form").addEventListener("submit", function (e) {
        const maxSize = 100 * 1024 * 1024;
        let totalSize = 0;

        const fileInputs = this.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            if (input.files.length > 0) {
                totalSize += input.files[0].size;
            }
        });

        if (totalSize > maxSize) {
            alert("Total file size exceeds 100MB. Please upload smaller files.");
            e.preventDefault();
        }
    });
</script>

</body>
</html>
