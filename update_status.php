<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'db_connect.php';
date_default_timezone_set('Asia/Manila');
require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip_id = $_POST['ip_id'];
    $new_status = $_POST['new_status'];
    $remarks = $_POST['remarks'] ?? '';

    $query = "SELECT title, tracking_id, email, department_id, campus_id FROM intellectual_properties WHERE ip_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ip_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (!$result) {
        die("Record not found!");
    }

    $title = $result['title'];
    $tracking_id = $result['tracking_id'];
    $email = $result['email'];
    $department_id = $result['department_id'];
    $campus_id = $result['campus_id'];

    // Update the status, remarks, and updated_at
    $updated_at = date('Y-m-d H:i:s');
    $update = $conn->prepare("UPDATE intellectual_properties SET status = ?, remarks = ?, updated_at = ? WHERE ip_id = ?");
    $update->bind_param("sssi", $new_status, $remarks, $updated_at, $ip_id);

    if (!$update->execute()) {
        die("Database update failed: " . $conn->error);
    }

    $notif_title = "{$title}";
    $notif_message = "has been updated to {$new_status}.";
    if (!empty($remarks)) {
        $notif_message .= " Remarks: {$remarks}";
    }

    $notif_query = $conn->prepare("INSERT INTO notifications (title, message, department_id, campus_id) VALUES (?, ?, ?, ?)");
    $notif_query->bind_param("sssi", $notif_title, $notif_message, $department_id, $campus_id);
    $notif_query->execute();

    // Send email notification
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  
        $mail->SMTPAuth = true;
        $mail->Username = 'mangjujutsu@gmail.com'; 
        $mail->Password = 'vqtk uhdc omsf otza';   
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('mangjujutsu@gmail.com', 'ITSO Admin');
        $mail->addAddress($email);

        $mail->isHTML(true);

        if ($new_status === 'Completed') {
            $mail->Subject = "Your Intellectual Property Application Has Been Approved";
            $mail->Body = "
                <p>Dear Applicant,</p>
                <p>Your Intellectual Property application has been <strong>approved</strong>:</p>
                <ul>
                    <li><strong>Tracking ID:</strong> {$tracking_id}</li>
                    <li><strong>Title:</strong> {$title}</li>
                </ul>
                <p>Thank you,<br>ITSO Office</p>
            ";
        } elseif ($new_status === 'Pending') {
            $mail->Subject = "Your Intellectual Property Application Has Been Disapproved";
            $mail->Body = "
                <p>Dear Applicant,</p>
                <p>We regret to inform you that your Intellectual Property application has been <strong>disapproved</strong>.</p>
                <ul>
                    <li><strong>Tracking ID:</strong> {$tracking_id}</li>
                    <li><strong>Title:</strong> {$title}</li>
                </ul>
                <p><strong>Remarks:</strong> {$remarks}</p>
                <p>Thank you for your understanding.</p>
            ";
        }

        $mail->send();
    } catch (Exception $e) {
        error_log("Email error: {$mail->ErrorInfo}");
    }

    if (isset($_POST['source'])) {
        $source = $_POST['source'];
        header("Location: $source");
        exit();
    } else {
        header("Location: admin_dashboard.php?page=all_applications");
        exit();
    }
}


?>
