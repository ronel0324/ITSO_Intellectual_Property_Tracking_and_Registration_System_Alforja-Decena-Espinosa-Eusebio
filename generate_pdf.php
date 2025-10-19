<?php
require 'dompdf/vendor/autoload.php';
use Dompdf\Dompdf;

if (isset($_GET['ip_id']) || isset($_POST['ip_id'])) {
    $ip_id = isset($_POST['ip_id']) ? $_POST['ip_id'] : $_GET['ip_id'];
    $ip_id = intval($ip_id);

    include __DIR__ . '/db_connect.php';

    if (!$conn) {
        die("Database connection failed.");
    }

    // 🔹 Fetch intellectual property with campus name
    $query = "SELECT ip.*, c.campus_name, d.department_name
              FROM intellectual_properties ip
              LEFT JOIN campuses c ON ip.campus_id = c.campus_id
              LEFT JOIN departments d ON ip.department_id = d.department_id
              WHERE ip.ip_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ip_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ip = $result->fetch_assoc();

    if (!$ip) {
        die("No IP record found.");
    }

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
        'CNAH' => 'College of Nursing and Allied Health'
    ];
    
    $departmentFullName = $departmentNames[$ip['department_id']] ?? $ip['department_id'];

    function formatDate($date) {
        return ($date && $date != '0000-00-00') ? date("F j, Y", strtotime($date)) : 'N/A';
    }

    $html = '
    <html>
    <head>
        <style>
            body { font-family: "Segoe UI", Tahoma, sans-serif; color: #333; padding: 20px; }
            h1 { text-align: center; color: #2c3e50; margin-bottom: 20px; }
            .card {
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 20px;
                background: #f9f9f9;
            }
            .label { font-weight: bold; color: #2c3e50; width: 30%; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            td { padding: 8px 10px; border-bottom: 1px solid #eee; }
            a.file-link { color: #1a73e8; text-decoration: none; }
            a.file-link:hover { text-decoration: underline; }
            .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #888; }
        </style>
    </head>
    <body>
        <h1>Intellectual Property Details</h1>

        <div class="card">
            <table>
                <tr><td class="label">Tracking ID</td><td>' . $ip['tracking_id'] . '</td></tr>
                <tr><td class="label">Campus</td><td>' . htmlspecialchars($ip['campus_name'] ?? "N/A") . '</td></tr>
                <tr><td class="label">Department</td><td>' . htmlspecialchars($departmentFullName) . '</td></tr>
                <tr><td class="label">Title</td><td>' . htmlspecialchars($ip['title']) . '</td></tr>
                <tr><td class="label">Authors</td><td>' . htmlspecialchars($ip['authors']) . '</td></tr>
                <tr><td class="label">Applicant</td><td>' . htmlspecialchars($ip['applicant_name']) . '</td></tr>
                <tr><td class="label">Classification</td><td>' . htmlspecialchars($ip['classification']) . '</td></tr>
                <tr><td class="label">Status</td><td>' . htmlspecialchars($ip['status']) . '</td></tr>
                <tr><td class="label">Submitted to IPOPHIL</td><td>' . ($ip['submitted'] ? 'Yes' : 'No') . '</td></tr>
                <tr><td class="label">Date Filed</td><td>' . formatDate($ip["date_submitted_to_itso"]) . '</td></tr>
                <tr><td class="label">Date Submitted</td><td>' . formatDate($ip["date_submitted_to_ipophil"]) . '</td></tr>
                <tr><td class="label">Expiration Date</td><td>' . formatDate($ip["expiration_date"]) . '</td></tr>
            </table>
        </div>

        <div class="card">
            <h3>Uploaded Files</h3>
            <table>';

    $files = [
        "Application Form" => $ip["application_form"], // 🔹 new field
        "Endorsement Letter" => $ip["endorsement_letter"],
        "Application Fee" => $ip["application_fee"],
        "Issued Certificate" => $ip["issued_certificate"],
        "Project File" => $ip["project_file"]
    ];

    foreach ($files as $label => $file) {
        if ($file) {
            $html .= '<tr><td class="label">' . $label . ':</td>
                        <td><a class="file-link" href="http://' . $_SERVER['HTTP_HOST'] . '/itso_enhance/uploads/' . htmlspecialchars($file) . '" target="_blank">View File</a>
                        </td></tr>';
        } else {
            $html .= '<tr><td class="label">' . $label . ':</td><td><em>Not uploaded</em></td></tr>';
        }
    }

    $html .= '
            </table>
        </div>

        <div class="footer">
            Generated by ITSO System • ' . date("F j, Y") . '
        </div>
    </body>
    </html>';

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream('IP_Details_' . $ip_id . '.pdf', ['Attachment' => false]); 

} else {
    echo "No IP selected.";
}

?>
