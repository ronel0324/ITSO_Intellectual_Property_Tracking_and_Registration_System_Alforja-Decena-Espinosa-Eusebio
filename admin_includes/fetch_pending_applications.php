<?php
include __DIR__ . '/../db_connect.php';

$sql = "
  SELECT ip.*, c.campus_name 
  FROM intellectual_properties ip
  LEFT JOIN campuses c ON ip.campus_id = c.campus_id
  WHERE ip.status = 'Pending'
  ORDER BY ip.ip_id DESC
";
$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
  $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
