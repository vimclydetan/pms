<?php
include '../admin/include/connect.php';
include '../tcpdf/tcpdf.php';

$patient_id = isset($_GET['viewid']) ? intval($_GET['viewid']) : 0;

if (!$patient_id) {
    die("Invalid patient ID.");
}

// Fetch patient data
$ret = mysqli_query($conn, "SELECT * FROM patient_record WHERE midwife_id IS NOT NULL AND id = '$patient_id'");
$patient = mysqli_fetch_array($ret);

$full_address = trim($patient['barangay'] . ', ' . $patient['city'] . ', ' . $patient['province']);

if (!$patient) {
    die("Patient not found.");
}

class MYPDF extends TCPDF
{
    public function Header()
    {
        // Add logo if exists
        $image_file = K_PATH_IMAGES . 'tcpdf/images/weblogo.png';
        if (file_exists($image_file)) {
            $this->Image($image_file, 15, 15, 25, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }

        // Push content lower down
        $this->SetY(15); // Start 15mm from the top
        $this->SetFont('helvetica', 'B', 20);
        $this->Cell(0, 10, 'Patient Medical Record', 0, 1, 'C');

        $this->SetFont('helvetica', 'I', 15);
        $this->Cell(0, 10, 'Grateful Beginnings Medical Clinic and Lying-in', 0, 1, 'C');

        $this->SetFont('helvetica', 'I', 11);
        $this->Cell(0, 10, '0006 LT National High Way Bgy Halang, Calamba, Philippines, 4027', 0, 1, 'C');

        // Add a separator line if desired
        $this->Ln(2);
        $this->Line(15, $this->GetY(), 195, $this->GetY()); // horizontal line
        $this->Ln(3);
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Medical Record - ' . $patient['patient_name']);
$pdf->SetMargins(PDF_MARGIN_LEFT, 40, PDF_MARGIN_RIGHT); // Increase top margin to 40
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage();

$html = '
<style>
    h3 {
        font-size: 16pt;
        color: #2E4053;
        text-align: center;
        margin-bottom: 10px;
        padding-bottom: 5px;
    }
    h4 {
        font-size: 13pt;
        color: #1F618D;
        background-color: #EBF5FB;
        padding: 5px 8px;
        margin-top: 15px;
        text-align: center;
    }
    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 10px;
    }
    th {
        background-color: #D6EAF8;
        font-weight: bold;
        text-align: left;
    }
    th, td {
        border: 0.5px solid #ABB2B9;
        padding: 6px 8px;
        font-size: 10.5pt;
    }
    tr:nth-child(even) {
        background-color: #F4F6F6;
    }
    .info-table td:first-child {
        width: 30%;
        font-weight: bold;
        background-color: #F9EBEA;
    }
</style>

<div style="margin-top:20px;"></div>
<h3>Patient Information</h3>
<table class="info-table">
    <tr><td>Name</td><td>' . htmlspecialchars($patient['patient_name']) . '</td></tr>
    <tr><td>Contact No.</td><td>' . htmlspecialchars($patient['contact_no']) . '</td></tr>
    <tr><td>Email</td><td>' . htmlspecialchars($patient['email']) . '</td></tr>
    <tr><td>Birthdate</td><td>' . htmlspecialchars($patient['birthdate']) . '</td></tr>
</table>
<br>';


// General Consultation
$gen_consult = mysqli_query($conn, "SELECT * FROM gen_consultation WHERE patient_record_id = '$patient_id'");
if (mysqli_num_rows($gen_consult) > 0):
    $html .= '<h4>General Consultation</h4>';
    $html .= '<table><thead><tr><th>Date</th><th>Reason for Visit</th></tr></thead><tbody>';
    while ($row = mysqli_fetch_assoc($gen_consult)):
        $html .= "<tr><td>{$row['date']}</td><td>{$row['reason_for_visit']}</td></tr>";
    endwhile;
    $html .= '</tbody></table><br>';
endif;

// Medical Records by Service
$serviceQuery = "
    SELECT 
        s.service_name, 
        f.field_name, 
        psr.field_value, 
        psr.date_served 
    FROM patient_service_records psr
    JOIN services s ON psr.service_id = s.service_id
    JOIN fields f ON psr.field_id = f.field_id
    WHERE psr.patient_record_id = '$patient_id'
    ORDER BY psr.date_served DESC
";
$result = mysqli_query($conn, $serviceQuery);
$records = [];
while ($row = mysqli_fetch_assoc($result)) {
    $records[$row['service_name']][] = $row;
}

if (!empty($records)):
    foreach ($records as $serviceName => $fields):
        $html .= "<h4>" . htmlspecialchars($serviceName) . "</h4>";
        $html .= '<table><thead><tr><th>Field</th><th>Value</th></tr></thead><tbody>';
        foreach ($fields as $field):
            $html .= "<tr><td>" . ucwords(str_replace('_', ' ', $field['field_name'])) . "</td><td>" . htmlspecialchars($field['field_value'] ?: '—') . "</td></tr>";
        endforeach;
        $html .= '</tbody></table><br>';
    endforeach;
else:
    $html .= '<p>No medical records available.</p>';
endif;

$html .= '<br><div style="font-size:9pt; text-align:right; color: #555;">
<i>Generated on: ' . date('F j, Y, g:i a') . '</i>
</div>';

$html .= '<div style="text-align:right; margin-top:30px;"><img src="../tcpdf/images/weblogo.png" width="80"></div>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('patient_medical_record_' . $patient_id . '.pdf', 'D'); // D = Download
exit;
