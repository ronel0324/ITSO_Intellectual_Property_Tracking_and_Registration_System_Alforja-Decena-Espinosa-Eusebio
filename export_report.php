<?php
require 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;

include 'db_connect.php';
require_once 'includes/jpgraph/src/jpgraph.php';
require_once 'includes/jpgraph/src/jpgraph_pie.php';
require_once 'includes/jpgraph/src/jpgraph_pie3d.php';

if (empty($_GET['campus'])) {
    die("Error: You must select a Campus to generate the PDF.");
}

$campusFilter = $_GET['campus'];
$departmentFilter = $_GET['department_id'] ?? null;
$monthFilter = $_GET['month'] ?? null;
$yearFilter = $_GET['year'] ?? null;

// Department full names
$departmentNames = [
    'CCS' => 'College of Computer Studies',
    'CFND' => 'College of Food Nutrition and Dietetics',
    'CIT' => 'College of Industrial Technology',
    'CTE' => 'College of Teacher Education',
    'CA' => 'College of Agriculture',
    'CAS' => 'College of Arts and Sciences',
    'CBAA' => 'College of Business Administration and Accountancy',
    'COE' => 'College of Engineering',
    'CCJE' => 'College of Criminal Justice Education',
    'COF' => 'College of Fisheries',
    'CHMT' => 'College of Hospitality Management and Tourism',
    'CNAH' => 'College of Nursing and Allied Health',
];

$campusName = 'N/A';
if ($campusFilter) {
    $campusSQL = "SELECT campus_name FROM campuses WHERE campus_id = '".$conn->real_escape_string($campusFilter)."' LIMIT 1";
    $campusResult = $conn->query($campusSQL);
    if ($campusResult && $campusResult->num_rows > 0) {
        $campusRow = $campusResult->fetch_assoc();
        $campusName = $campusRow['campus_name'];
    }
}

$where = "WHERE ip.campus_id = '".$conn->real_escape_string($campusFilter)."'";
if ($departmentFilter) $where .= " AND ip.department_id = '".$conn->real_escape_string($departmentFilter)."'";
if ($monthFilter) $where .= " AND MONTH(ip.date_submitted_to_itso) = '".intval($monthFilter)."'";
if ($yearFilter) $where .= " AND YEAR(ip.date_submitted_to_itso) = '".intval($yearFilter)."'";

// --- Query for chart ---
$chartSQL = "SELECT ip.status, COUNT(*) as total 
             FROM intellectual_properties ip 
             $where 
             GROUP BY ip.status";
$chartResult = $conn->query($chartSQL);
$statusCounts = ['Pending' => 0, 'Ongoing' => 0, 'Completed' => 0];
while ($row = $chartResult->fetch_assoc()) {
    if (isset($statusCounts[$row['status']])) $statusCounts[$row['status']] = (int)$row['total'];
}

// --- Generate Pie Chart ---
$data = array_values($statusCounts);
$labels = array_keys($statusCounts);

$graph = new PieGraph(350,250);
$graph->SetShadow();
$graph->title->SetFont(FF_FONT1, FS_BOLD);

$p1 = new PiePlot3D($data);
$p1->SetLegends($labels);
$p1->SetCenter(0.5);
$p1->SetSize(0.3);

$statusColors = ['Pending'=>'DC3545','Ongoing'=>'FFC107','Completed'=>'28A745'];
$sliceColors = [];
foreach ($labels as $label) $sliceColors[] = $statusColors[$label] ?? 'CCCCCC';
$p1->SetSliceColors($sliceColors);
$p1->value->SetFormat('%.1f%%');
$p1->value->Show();
$graph->Add($p1);


$legendLabels = [];
foreach ($labels as $i => $label) $legendLabels[] = $label . ' (' . $data[$i] . ')';
$p1->SetLegends($legendLabels);

// --- Fetch data ---
$sql = "SELECT ip.*, c.campus_name, d.department_name
        FROM intellectual_properties ip 
        LEFT JOIN campuses c ON ip.campus_id = c.campus_id 
        LEFT JOIN departments d ON ip.department_id = d.department_id
        $where 
        ORDER BY ip.classification, ip.date_submitted_to_itso DESC";
$result = $conn->query($sql);

$groupedData = [];
while ($row = $result->fetch_assoc()) {
    $dept = $row['department_id'] ?? 'Unspecified department_id';
    $class = $row['classification'] ?? 'Unspecified Classification';
    $groupedData[$dept][$class][] = $row;
}

// --- Capture chart image ---
$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
ob_start();
imagepng($gdImgHandler);
$imageData = ob_get_clean();
imagedestroy($gdImgHandler);
$base64Image = base64_encode($imageData);

// --- PDF HTML ---
$html = "<style>
@font-face { font-family: 'OldeEnglish'; src: url('http://localhost/itso_tracking_system/fonts/OldeEnglish.ttf') format('truetype'); }
.header-container { width:100%; margin-bottom:20px; }
.logo{width:80px;} .logo1{width:55px;}
table{border-collapse:collapse;width:100%; margin-bottom:30px;font-family:Arial,sans-serif;}
th,td{border:1px solid #000;padding:5px;font-size:12px;}
thead{background-color:#f2f2f2;}
h1,h3,h4{font-family:Arial,sans-serif;margin:10px 0;}
h1{text-align:center;font-size:16px;}
h3{font-size:16px;} h4{font-size:12px;}
</style>";

$html .= "<div class='header-container'>
<table style='width:100%;margin-bottom:10px;'><tr>
<td style='width:80px;text-align:center;'><img src='http://localhost/itso_tracking_system/assets/imgs/logo.png' class='logo'></td>
<td style='text-align:left;padding-left:10px;'>
<p style='text-align:center;font-size:16px;margin:0;'>Republic of the Philippines</p>
<p style='text-align:center;font-size:28px;margin:0;font-weight:bold;'>Laguna State Polytechnic University</p>
<p style='text-align:center;font-size:16px;margin:0;'>Province of Laguna</p>
</td>
<td style='width:80px;text-align:center;'><img src='http://localhost/itso_tracking_system/assets/imgs/bglogo.png' class='logo1'></td>
</tr></table>
<h1>Innovation Technology Support Office <h4 style='text-align:center;'>(".htmlspecialchars($campusName).")</h4></h1>

<div style='text-align:center;margin-bottom:30px;'>
<img src='data:image/png;base64,$base64Image' style='max-width:40%;' alt='Status Chart'>
</div></div>";

foreach ($groupedData as $dept => $classifications) {
    $fullDeptName = $departmentNames[$dept] ?? $dept;
    $html .= "<h3>".htmlspecialchars($fullDeptName)."</h3>";
    foreach ($classifications as $class => $records) {
        $html .= "<h4>".htmlspecialchars($class)."</h4><table><thead>
<tr><th>Tracking ID</th><th>Title</th><th>Classification</th><th>Campus</th><th>Department</th><th>Authors</th><th>Status</th><th>Filing Date</th></tr>
</thead><tbody>";
        foreach ($records as $row) {
            $fullDept = $departmentNames[$row['department_id']] ?? $row['department_id'];
            $html .= "<tr>
<td>".htmlspecialchars($row['tracking_id'])."</td>
<td>".htmlspecialchars($row['title'])."</td>
<td>".htmlspecialchars($row['classification'])."</td>
<td>".htmlspecialchars($row['campus_name'] ?? 'N/A')."</td>
<td>".htmlspecialchars($fullDept)."</td>
<td>".htmlspecialchars($row['authors'])."</td>
<td>".htmlspecialchars($row['status'])."</td>
<td>".date("F j, Y", strtotime($row['date_submitted_to_itso']))."</td>
</tr>";
        }
        $html .= "</tbody></table>";
    }
}

$options = new \Dompdf\Options();
$options->set('defaultFont', 'OldeEnglish');
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','portrait');
$dompdf->render();
$dompdf->stream("ip_monitoring_report.pdf", ["Attachment"=>false]);
?>
