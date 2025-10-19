<?php

if (session_status() == PHP_SESSION_NONE) session_start();
include 'db_connect.php';

if (!isset($_SESSION['username']) || 
   ($_SESSION['role'] != "Chairperson" && $_SESSION['role'] != "Director")) {
    header("Location: login.php");
    exit();
}

$groupedData = [];

// Get filters
$campusFilter = isset($_GET['campus']) ? $conn->real_escape_string($_GET['campus']) : null;
$departmentFilter = isset($_GET['department_id']) ? $conn->real_escape_string($_GET['department_id']) : null;
$monthFilter = isset($_GET['month']) && $_GET['month'] !== '' ? $_GET['month'] : null;
$yearFilter = isset($_GET['year']) && !empty($_GET['year']) ? (int) $_GET['year'] : null;

// Only proceed when campus is selected
if ($campusFilter) {
    $whereClauses = [];
    $whereClauses[] = "ip.campus_id = '$campusFilter'"; // campus mandatory

    if (!empty($departmentFilter)) {
        $whereClauses[] = "ip.department_id = '$departmentFilter'";
    }
    if ($yearFilter) {
        $whereClauses[] = "YEAR(ip.date_submitted_to_itso) = '$yearFilter'";
    }
    if ($monthFilter) {
        $whereClauses[] = "MONTH(ip.date_submitted_to_itso) = '$monthFilter'";
    }


    $whereSql = implode(" AND ", $whereClauses);
    $sql = "
        SELECT ip.*, d.department_name
        FROM intellectual_properties ip
        LEFT JOIN departments d ON ip.department_id = d.department_id
        WHERE $whereSql
        ORDER BY ip.classification, ip.date_submitted_to_itso DESC
    ";

    $result = $conn->query($sql);

    // Group by classification
    while ($row = $result->fetch_assoc()) {
        $class = $row['classification'];
        $groupedData[$class][] = $row;
    }
}

// Export URL generator
$exportUrl = 'export_report.php';
$queryParams = [];
if ($campusFilter) $queryParams['campus'] = $campusFilter;
if ($departmentFilter) $queryParams['department_id'] = $departmentFilter;
if ($monthFilter) $queryParams['month'] = $monthFilter;
if ($yearFilter) $queryParams['year'] = $yearFilter;

if (!empty($queryParams)) {
    $exportUrl .= '?' . http_build_query($queryParams);
}

?>

<style>
/* --- Styling --- */
.reports-wrapper { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin: 10px; }
.reports-wrapper h2 { margin-bottom: 15px; font-size: 26px; font-weight: bold; color: #333; }
.campus-btn { background-color: #ffffffc6; color: #000; border: 2px solid #0000009e; padding: 10px 18px; margin: 5px; border-radius: 6px; text-decoration: none; display: inline-block; font-size: 15px; transition: 0.3s; }
.campus-btn:hover, .campus-btn.active { background-color: #16a34ab5; }
.export-btn { background-color: #007bffc6; color: white; padding: 6px 12px; font-size: 14px; margin: 10px 0; text-decoration: none; border-radius: 6px; display: inline-block; transition: 0.3s; }
.export-btn:hover { background-color: #16a34ab5; }
.filterpos { margin: 15px 0; text-align: left; }
.filterpos select { padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 15px; }
.ip-table { width: 100%; border-collapse: collapse; margin: 20px 0; border-radius: 8px; overflow: hidden; }
.ip-table th { background: #1e3a2e; color: white; padding: 12px; text-align: left; }
.ip-table td { padding: 10px; border-bottom: 1px solid #ddd; }
.ip-table tr:nth-child(even) { background: #f9f9f9; }
.status-completed { background-color: #28a745; color: white; padding: 4px 8px; border-radius: 6px; font-size: 13px; }
.status-pending { background-color: #dc3545; color: white; padding: 4px 8px; border-radius: 6px; font-size: 13px; }
.status-ongoing { background-color: #ffc107; color: black; padding: 4px 8px; border-radius: 6px; font-size: 13px; }
</style>

<div class="reports-wrapper">
    <h2>Intellectual Properties Report</h2>

    <!-- Export button (enabled if campus selected) -->
    <?php if ($campusFilter): ?>
        <a href="<?= $exportUrl ?>" class="export-btn" target="_blank">Generate PDF</a>
    <?php else: ?>
        <button class="export-btn" disabled title="Please select a Campus">Generate PDF</button>
    <?php endif; ?>

    <!-- Year Filter -->
    <div class="filterpos">
        <form method="GET">
            <input type="hidden" name="page" value="reports">
            <label><b>Select Year:</b></label>
            <select name="year" onchange="this.form.submit()">
                <?php
                $currentYear = date('Y');
                for ($y = $currentYear; $y >= 2015; $y--) {
                    $selected = (isset($_GET['year']) && $_GET['year'] == $y) ? 'selected' : '';
                    echo "<option value='$y' $selected>$y</option>";
                }
                ?>
            </select>
        </form>
    </div>

    <!-- Campus Buttons -->
    <div>
        <?php
        $campusResult = $conn->query("SELECT * FROM campuses ORDER BY campus_name ASC");
        while ($campus = $campusResult->fetch_assoc()):
            $isActive = ($campusFilter == $campus['campus_id']) ? "active" : "";
        ?>
            <a href="?page=reports&campus=<?= $campus['campus_id'] ?><?= isset($_GET['year']) ? '&year=' . $_GET['year'] : '' ?><?= isset($_GET['department_id']) ? '&department_id=' . urlencode($_GET['department_id']) : '' ?>"
               class="campus-btn <?= $isActive ?>">
               <?= htmlspecialchars($campus['campus_name']) ?>
            </a>
        <?php endwhile; ?>
    </div>

    <!-- Department Filter -->
    <?php if ($campusFilter): ?>
    <div class="filterpos">
        <form method="GET">
            <input type="hidden" name="page" value="reports">
            <input type="hidden" name="campus" value="<?= $campusFilter ?>">
            <input type="hidden" name="year" value="<?= isset($_GET['year']) ? $_GET['year'] : date('Y') ?>">
            <label for="department_id"><b>Filter by Department:</b></label>
            <select name="department_id" id="department_id" onchange="this.form.submit()">
                <option value="">-- All Departments --</option>
                <?php
                $dept_result = $conn->query("
                    SELECT DISTINCT d.department_id, d.department_name
                    FROM departments d
                    INNER JOIN intellectual_properties ip ON ip.department_id = d.department_id
                    WHERE ip.campus_id = '$campusFilter'
                    ORDER BY d.department_name ASC
                ");
                while ($row = $dept_result->fetch_assoc()) {
                    $selected = ($departmentFilter === $row['department_id']) ? 'selected' : '';
                    echo "<option value='{$row['department_id']}' $selected>{$row['department_name']}</option>";
                }
                ?>
            </select>
        </form>
    </div>
    <?php endif; ?>

    <!-- Reports Table -->
    <?php if (!empty($groupedData)): ?>
        <?php foreach ($groupedData as $class => $records): ?>
            <h4><?= htmlspecialchars($class) ?></h4>
            <table class="ip-table">
                <thead>
                    <tr>
                        <th>Tracking ID</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Department</th>
                        <th>Authors</th>
                        <th>Status</th>
                        <th>Filing Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $row): ?>
                        <tr>
                            <td><?= $row['tracking_id'] ?></td>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= htmlspecialchars($row['classification']) ?></td>
                            <td><?= htmlspecialchars($row['department_name']) ?></td>
                            <td><?= htmlspecialchars($row['authors']) ?></td>
                            <td>
                                <?php
                                    $status = $row['status'];
                                    $classStatus = $status == 'Completed' ? 'status-completed' :
                                                   ($status == 'Pending' ? 'status-pending' : 'status-ongoing');
                                    echo "<span class='$classStatus'>$status</span>";
                                ?>
                            </td>
                            <td><?= date("F j, Y", strtotime($row['date_submitted_to_itso'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="margin-top:15px; color:#666;">
            <?php if (!$campusFilter): ?>
                Please select a campus to view reports.
            <?php elseif (!$departmentFilter): ?>
                Showing all departments under the selected campus.
            <?php else: ?>
                No reports found for your selection.
            <?php endif; ?>
        </p>
    <?php endif; ?>

</div>
