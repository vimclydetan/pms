<?php 
include '../admin/include/connect.php';
include '../tcpdf/tcpdf.php';

$selected_month = isset($_GET['month']) && preg_match('/^\d{2}$/', $_GET['month']) ? $_GET['month'] : null;
$selected_year = isset($_GET['year']) && is_numeric($_GET['year']) ? intval($_GET['year']) : null;

// Get service records
$service_sql = "
    SELECT 
        pr.id AS patient_id,
        pr.patient_name,
        TIMESTAMPDIFF(YEAR, pr.birthdate, CURDATE()) AS age,
        CONCAT(pr.barangay, ', ', pr.city) AS address,
        pr.contact_no,
        s.service_name,
        r.date_served,
        CASE
            WHEN o.name IS NOT NULL THEN o.name
            WHEN m.name IS NOT NULL THEN m.name
            ELSE 'N/A'
        END AS handled_by,
        'SERVICE' AS record_type
    FROM patient_service_records r
    JOIN services s ON r.service_id = s.service_id
    JOIN patient_record pr ON r.patient_record_id = pr.id
    LEFT JOIN obgyn o ON pr.obgyn_id = o.id
    LEFT JOIN midwife m ON pr.midwife_id = m.id
    WHERE 1=1
";

// Get general consultation records with midwife/OBGYN info
$consultation_sql = "
    SELECT 
        pr.id AS patient_id,
        pr.patient_name,
        TIMESTAMPDIFF(YEAR, pr.birthdate, CURDATE()) AS age,
        CONCAT(pr.barangay, ', ', pr.city) AS address,
        pr.contact_no,
        'Consultation' AS service_name,
        gc.date AS date_served,
        CASE
            WHEN o.name IS NOT NULL THEN o.name
            WHEN m.name IS NOT NULL THEN m.name
            ELSE 'N/A'
        END AS handled_by,
        'CONSULTATION' AS record_type
    FROM gen_consultation gc
    JOIN patient_record pr ON gc.patient_record_id = pr.id
    LEFT JOIN obgyn o ON pr.obgyn_id = o.id
    LEFT JOIN midwife m ON pr.midwife_id = m.id
    WHERE 1=1
";

// Add date filters
$date_filter_service = "";
$date_filter_consultation = "";
$params_service = [];
$params_consultation = [];
$types_service = '';
$types_consultation = '';

if ($selected_year && $selected_month) {
    $date_filter_service = " AND DATE_FORMAT(r.date_served, '%Y-%m') = ?";
    $date_filter_consultation = " AND DATE_FORMAT(gc.date, '%Y-%m') = ?";
    $types_service = 's';
    $types_consultation = 's';
    $params_service[] = $selected_year . '-' . $selected_month;
    $params_consultation[] = $selected_year . '-' . $selected_month;
} elseif ($selected_year) {
    $date_filter_service = " AND YEAR(r.date_served) = ?";
    $date_filter_consultation = " AND YEAR(gc.date) = ?";
    $types_service = 'i';
    $types_consultation = 'i';
    $params_service[] = $selected_year;
    $params_consultation[] = $selected_year;
} elseif ($selected_month) {
    $date_filter_service = " AND MONTH(r.date_served) = ?";
    $date_filter_consultation = " AND MONTH(gc.date) = ?";
    $types_service = 'i';
    $types_consultation = 'i';
    $params_service[] = $selected_month;
    $params_consultation[] = $selected_month;
}

$service_sql .= $date_filter_service;
$consultation_sql .= $date_filter_consultation;

// Execute service records query
$stmt_service = mysqli_prepare($conn, $service_sql);
if (!empty($params_service)) {
    mysqli_stmt_bind_param($stmt_service, $types_service, ...$params_service);
}
mysqli_stmt_execute($stmt_service);
$service_result = mysqli_stmt_get_result($stmt_service);

// Execute consultation records query
$stmt_consultation = mysqli_prepare($conn, $consultation_sql);
if (!empty($params_consultation)) {
    mysqli_stmt_bind_param($stmt_consultation, $types_consultation, ...$params_consultation);
}
mysqli_stmt_execute($stmt_consultation);
$consultation_result = mysqli_stmt_get_result($stmt_consultation);

// Combine results
$patients = [];

// Add service records
while ($row = mysqli_fetch_assoc($service_result)) {
    $patients[] = $row;
}

// Add consultation records
while ($row = mysqli_fetch_assoc($consultation_result)) {
    $patients[] = $row;
}

// Sort by date served
usort($patients, function($a, $b) {
    return strtotime($b['date_served']) - strtotime($a['date_served']);
});

//PDF
$legal = array('w' => 355.6, 'h' => 215.9);
$pdf = new TCPDF('L', PDF_UNIT, $legal, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Patients Report');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(10,10,10);
$pdf->AddPage();
$pdf->SetFont('helvetica', '',10);

$pdf->Cell(0,10,'PATIENTS REPORT',0,1,'C');

if ($selected_month && $selected_year) {
    $filterText = date("F Y", mktime(0,0,0,$selected_month, 1, $selected_year));
    $pdf->Cell(0,10,$filterText,0,1,'C');
    $pdf->Ln();
} elseif ($selected_month) {
    $filterText = date("F", mktime(0,0,0, $selected_month, 1));
    $pdf->Cell(0,10,"Month: " . $filterText,0,1,'C');
    $pdf->Ln();
} elseif ($selected_year) {
    $pdf->Cell(0,10,"Year: " . $selected_year,0,1,'C');
    $pdf->Ln();
}

$headers = ['NO.', 'PATIENT NAME', 'AGE', 'ADDRESS', 'CONTACT NO.', 'SERVICE/CONSULTATION', 'MD', 'DATE SERVED'];
$columnWidths = [10,40,15,60,35,50,40,30];

foreach ($headers as $index => $header_name) {
    $pdf->Cell($columnWidths[$index], 10, $header_name, 1, 0, 'C');
}
$pdf->Ln();

$count = 1;
foreach($patients as $p) {
    $rowData = [
        $count++,
        $p['patient_name'],
        $p['age'],
        $p['address'],
        $p['contact_no'],
        $p['service_name'],
        $p['handled_by'],
        $p['date_served']
    ];

    foreach ($rowData as $index => $cell) {
        $pdf->Cell($columnWidths[$index], 10, $cell, 1, 0, 'C');
    }
    $pdf->Ln();
}

$pdf->Output('all_patients_report.pdf', 'I');
exit;
?>