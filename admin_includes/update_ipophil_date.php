<?php
include '../db_connect.php';

// Get JSON data
$data = json_decode(file_get_contents("php://input"), true);
$ip_id = $data['ip_id'];
$date_submitted_to_ipophil = $data['date_submitted_to_ipophil'];

// First, get classification from DB
$result = $conn->query("SELECT classification FROM intellectual_properties WHERE ip_id = $ip_id");
$row = $result->fetch_assoc();
$classification = $row['classification'];

// Function to calculate expiration
function calculateExpirationDate($classification, $dateSubmitted) {
    if (empty($dateSubmitted) || $dateSubmitted === '0000-00-00') return null;
    $yearsToAdd = 0;
    switch (strtolower($classification)) {
        case 'copyright': $yearsToAdd = 50; break;
        case 'patent': $yearsToAdd = 20; break;
        case 'trademark': $yearsToAdd = 10; break;
        default: $yearsToAdd = 0;
    }
    return $yearsToAdd > 0 ? date('Y-m-d', strtotime("+$yearsToAdd years", strtotime($dateSubmitted))) : null;
}

$expiration_date = calculateExpirationDate($classification, $date_submitted_to_ipophil);

// Update both columns
$stmt = $conn->prepare("UPDATE intellectual_properties SET date_submitted_to_ipophil=?, expiration_date=? WHERE ip_id=?");
$stmt->bind_param("ssi", $date_submitted_to_ipophil, $expiration_date, $ip_id);

if($stmt->execute()) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false]);
}
?>
