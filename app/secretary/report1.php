<?php 
include '../admin/include/connect.php';
include '../tcpdf/tcpdf.php';

$selected_month = isset($_GET['month']) && preg_match('/^\d{2}$/', $_GET['month']) ? $_GET['month'] : null;
$selected_year = isset($_GET['year']) && is_numeric($_GET['year']) ? intval($_GET['year']) : null;

function get_field_id($conn, $service_name, $field_name) {
    $sql = "SELECT f.field_id FROM fields f JOIN services s ON f.service_id = s.service_id WHERE s.service_name = ? AND f.field_name = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $service_name, $field_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['field_id'] ?? null;
}

// Get relevant field IDs
$delivery_date_fid = get_field_id($conn, 'OB Assisted Delivery', 'Delivery Date');
$delivery_date_midwife_fid = get_field_id($conn, 'Midwife Assisted Delivery', 'Delivery Date');
$estimated_due_date_fid = get_field_id($conn, 'BPS with NST', 'Estimated Due Date');

// Query Delivered Patients (Join with OBGYN and Midwife tables)
$delivered_sql = "
    SELECT 
        pr.id AS patient_id,
        pr.patient_name,
        TIMESTAMPDIFF(YEAR, pr.birthdate, CURDATE()) AS age,
        CONCAT(pr.barangay, ', ', pr.city) AS address,
        pr.contact_no,
        r.field_value AS delivery_date,
        CASE 
            WHEN s.service_id = 14 THEN o.name 
            WHEN s.service_id = 15 THEN m.name 
            ELSE 'Unknown'
        END AS handled_by,
        'Delivered' AS classification,
        r.date_served
    FROM patient_service_records r
    JOIN services s ON r.service_id = s.service_id
    JOIN patient_record pr ON r.patient_record_id = pr.id
    LEFT JOIN obgyn o ON pr.obgyn_id = o.id
    LEFT JOIN midwife m ON pr.midwife_id = m.id
    WHERE r.field_id IN (?, ?)
";

if ($selected_year && $selected_month) {
    $delivered_sql .= " AND DATE_FORMAT(r.date_served, '%Y-%m') = CONCAT(?, '-', ?)";
} elseif ($selected_year) {
    $delivered_sql .= " AND YEAR(r.date_served) = ?";
} elseif ($selected_month) {
    $delivered_sql .= " AND MONTH(r.date_served) = ?";
}

$stmt = mysqli_prepare($conn, $delivered_sql);

if ($selected_year && $selected_month) {
    mysqli_stmt_bind_param($stmt, 'iiss', $delivery_date_fid, $delivery_date_midwife_fid, $selected_year, $selected_month);
} elseif ($selected_year) {
    mysqli_stmt_bind_param($stmt, 'iii', $delivery_date_fid, $delivery_date_midwife_fid, $selected_year);
} elseif ($selected_month) {
    mysqli_stmt_bind_param($stmt, 'iis', $delivery_date_fid, $delivery_date_midwife_fid, $selected_month);
} else {
    mysqli_stmt_bind_param($stmt, 'ii', $delivery_date_fid, $delivery_date_midwife_fid);
}

// Query Expectant Patients (from BPS with NST)
$expectant_sql = "
    SELECT 
        pr.id AS patient_id,
        pr.patient_name,
        TIMESTAMPDIFF(YEAR, pr.birthdate, CURDATE()) AS age,
        CONCAT(pr.barangay, ', ', pr.city) AS address,
        pr.contact_no,
        r.field_value AS estimated_due_date,
        CASE
            WHEN o.name IS NOT NULL THEN o.name
            WHEN m.name IS NOT NULL THEN m.name
            ELSE 'N/A'
        END AS handled_by,
        'Expectant' AS classification,
        r.date_served
    FROM patient_service_records r
    JOIN services s ON r.service_id = s.service_id
    JOIN patient_record pr ON r.patient_record_id = pr.id
    LEFT JOIN obgyn o ON pr.obgyn_id = o.id
    LEFT JOIN midwife m ON pr.midwife_id = m.id
    WHERE r.field_id = ?
      AND STR_TO_DATE(r.field_value, '%Y-%m-%d') > CURDATE()
";

if ($selected_year && $selected_month) {
    $expectant_sql .= " AND DATE_FORMAT(r.date_served, '%Y-%m') = CONCAT(?, '-', ?)";
} elseif ($selected_year) {
    $expectant_sql .= " AND YEAR(r.date_served) = ?";
} elseif ($selected_month) {
    $expectant_sql .= " AND MONTH(r.date_served) = ?";
}

$stmt2 = mysqli_prepare($conn, $expectant_sql);

if ($selected_year && $selected_month) {
    mysqli_stmt_bind_param($stmt2, 'iss', $estimated_due_date_fid, $selected_year, $selected_month);
} elseif ($selected_year) {
    mysqli_stmt_bind_param($stmt2, 'is', $estimated_due_date_fid, $selected_year);
} elseif ($selected_month) {
    mysqli_stmt_bind_param($stmt2, 'is', $estimated_due_date_fid, $selected_month);
} else {
    mysqli_stmt_bind_param($stmt2, 'i', $estimated_due_date_fid);
}

// Execute the prepared statements
mysqli_stmt_execute($stmt);
$delivered_result = mysqli_stmt_get_result($stmt);

mysqli_stmt_execute($stmt2);
$expectant_result = mysqli_stmt_get_result($stmt2);

// Combine Results
$patients = [];

while ($row = mysqli_fetch_assoc($delivered_result)) {
    $patients[] = $row;
}
while ($row = mysqli_fetch_assoc($expectant_result)) {
    $patients[] = $row;
}

// Sort by classification
usort($patients, function($a, $b) {
    return strcmp($a['classification'], $b['classification']);
});

// Generate PDF
$legal = array('w' => 355.6, 'h' => 215.9); // Legal size in mm
$pdf = new TCPDF('L', PDF_UNIT, $legal, true, 'UTF-8', false); // Landscape

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Parturition CENSUS');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(10, 10, 10); // Left, Top, Right
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);

// Title
$pdf->Cell(0, 10, 'PARTURITION CENSUS', 0, 1, 'C');

if ($selected_month && $selected_year) {
    $filterText = date("F Y", mktime(0, 0, 0, $selected_month, 1, $selected_year));
    $pdf->Cell(0, 10, $filterText, 0, 1, 'C');
    $pdf->Ln();
} elseif ($selected_month) {
    $filterText = date("F", mktime(0, 0, 0, $selected_month, 1));
    $pdf->Cell(0, 10, "Month: " . $filterText, 0, 1, 'C');
    $pdf->Ln();
} elseif ($selected_year) {
    $pdf->Cell(0, 10, "Year: " . $selected_year, 0, 1, 'C');
    $pdf->Ln();
}


$headers = ['NO.', 'PATIENT\'S NAME', 'AGE', 'ADDRESS', 'CONTACT NO.', 'CLASSIFICATION', 'MD', 'DELIVERY DATE'];
$columnWidths = [10, 40, 15, 60, 35, 40, 40, 30];

foreach ($headers as $index => $header) {
    $pdf->Cell($columnWidths[$index], 10, $header, 1, 0, 'C');
}
$pdf->Ln();

$count = 1;
foreach ($patients as $p) {
    $rowData = [
        $count++,
        $p['patient_name'],
        $p['age'],
        $p['address'],
        $p['contact_no'],
        $p['classification'],
        $p['handled_by'],
        $p['classification'] === 'Delivered' ? $p['delivery_date'] : $p['estimated_due_date']
    ];

    foreach ($rowData as $index => $cell) {
        $pdf->Cell($columnWidths[$index], 10, $cell, 1, 0, 'C');
    }
    $pdf->Ln();
}

// Output PDF inline in browser
$pdf->Output('delivered_and_expectant.pdf', 'I');
exit;
?>