<?php
session_start();
include '../admin/include/connect.php';

//Retrieve form data
$serviceSelector = $_POST['serviceSelector'] ?? null;

if (!$serviceSelector) {
    die("Error: No service selected.");
}

//Map service names to database-friendly values
$serviceMapping = [
    'us_transvaginal' => 'Transvaginal Ultrasound',
    'us_pelvic' => 'Pelvic Ultrasound',
    'us_bps' => 'BPS with NST',
    'pap_smear' => 'Pap Smear',
    'preg_test' => 'Pregnancy Test',
    'iud_insert' => 'IUD Insertion',
    'iud_removal' => 'IUD Removal',
    'implant_insertion' => 'Implant Insertion',
    'implant_removal' => 'Implant Removal',
    'flu_vaccine' => 'Flu Vaccine',
    'cervarix' => 'Cervarix',
    'gardasil' => 'Gardasil',
    'dmpa' => 'DMPA',
    'midwife_delivery' => 'Midwife Assisted Delivery',
    'ob_delivery' => 'OB Assisted Delivery',
];

$serviceName = $serviceMapping[$serviceSelector] ?? null;
if (!$serviceName) {
    die("Error: Invalid service selected.");
}

//Insert or retrieve the service ID
$query = "SELECT service_id FROM services WHERE service_name = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $serviceName);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $service = $result->fetch_assoc();
    $serviceId = $service['service_id'];
} else {
    //Insert new service if it doesn't exist
    $insertQuery = "INSERT INTO services (service_name) VALUES (?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("s", $serviceName);
    $stmt->execute();
    $serviceId = $stmt->insert_id;
}

//Process dynamic fields based on the selected service
$fieldData = [];
switch ($serviceSelector) {

    case 'us_transvaginal':
        $fieldData = [
            'Pregnant?' => ['value' => $_POST['us_transvaginal_preg'], 'type' => 'select'],
            'Notes' => ['value' => $_POST['us_transvaginal_notes'], 'type' => 'textarea'],
        ];
        break;

    case 'us_pelvic':
        $fieldData = [
            'Notes' => ['value' => $_POST['us_pelvic_notes'], 'type' => 'textarea'],
        ];
        break;

    case 'us_bps':
        $fieldData = [
            'Estimated Due Date' => ['value' => $_POST['us_bps_edd'], 'type' => 'date'],
            'Fetal heartbeat per bpm' => ['value' => $_POST['us_bps_beat'], 'type' => 'text'],
            'Weeks of Gestation' => ['value' => $_POST['us_bps_weeks'], 'type' => 'text'],
            'Notes' => ['value' => $_POST['us_bps_notes'], 'type' => 'textarea'],
        ];
        break;

    case 'pap_smear':
        $fieldData = [
            'Menstrual Cycle' => ['value' => $_POST['pap_mens'], 'type' => 'textarea'],
            'Sex Contact' => ['value' => $_POST['pap_sex'], 'type' => 'date'],
            'Notes' => ['value' => $_POST['pap_notes'], 'type' => 'textarea'],
        ];
        break;

    case 'preg_test':
        $fieldData = [
            'Method' => ['value' => $_POST['preg_method'], 'type' => 'select'],
            'Result' => ['value' => $_POST['preg_result'], 'type' => 'select'],
            'Notes' => ['value' => $_POST['preg_notes'], 'type' => 'textarea'],
        ];
        break;

    case 'iud_insert':
        $fieldData = [
            'First Day of Menstruation' => ['value' => $_POST['iud_insertion_first'], 'type' => 'date'],
            'Last Day of Menstruation' => ['value' => $_POST['iud_insertion_last'], 'type' => 'date'],
            'Date of Insertion' => ['value' => $_POST['iud_insertion_date'], 'type' => 'date'],
            'Notes' => ['value' => $_POST['iud_insert_notes'], 'type' => 'textarea'],
        ];
        break;

    case 'iud_removal':
        $fieldData = [
            'First Day of Menstruation' => ['value' => $_POST['iud_removal_first'], 'type' => 'date'],
            'Last Day of Menstruation' => ['value' => $_POST['iud_removal_last'], 'type' => 'date'],
            'Date of Removal' => ['value' => $_POST['iud_removal_date'], 'type' => 'date'],
            'Reason for Removal' => ['value' => $_POST['iud_removal_reason'], 'type' => 'textarea'],
            'Notes' => ['value' => $_POST['iud_removal_notes'], 'type' => 'textarea'],
        ];
        break;

    case 'implant_insertion':
        $fieldData = [
            'History of first contraceptive' => ['value' => $_POST['implant_insertion_history'], 'type' => 'textarea'],
            'Notes' => ['value' => $_POST['implant_insertion_notes'], 'type' => 'textarea'],
        ];
        break;

    case 'implant_removal':
        $fieldData = [
            'History of first contraceptive' => ['value' => $_POST['implant_removal_history'], 'type' => 'textarea'],
            'Notes' => ['value' => $_POST['implant_removal_notes'], 'type' => 'textarea'],
        ];
        break;

    case 'flu_vaccine':
        $fieldData = [
            'Allergic reactions?' => ['value' => $_POST['flu_allergic'], 'type' => 'textarea'],
            'First Dose' => ['value' => $_POST['flu_first_date'], 'type' => 'date'],
            'Last Dose' => ['value' => $_POST['flu_last_date'], 'type' => 'date'],
            'Notes' => ['value' => $_POST['flu_notes'], 'type' => 'textarea'],
        ];
        break;

    case 'dmpa':
        $fieldData = [
            'Month Selected' => ['value' => $_POST['dmpa_month'], 'type' => 'select'],
            'Last Inject Date' => ['value' => $_POST['dmpa_last_date'], 'type' => 'date'],
            'Next Due Date' => ['value' => $_POST['dmpa_next_date'], 'type' => 'date'],
            'Side Effects(if any)' => ['value' => $_POST['dmpa_notes'], 'type' => 'textarea'],
        ];
        break;

    case 'cervarix':
        $fieldData = [
            'Dose' => ['value' => $_POST['cervarix_dose'], 'type' => 'select'],
            'Next Dose Due' => ['value' => $_POST['cervarix_next_date'], 'type' => 'date'],
            'Notes' => ['value' => $_POST['cervarix_notes'], 'type' => 'textarea'],
        ];
        break;

    case 'gardasil':
        $fieldData = [
            'Gardasil Type' => ['value' => $_POST['gardasil_type'], 'type' => 'select'],
            'Date Given' => ['value' => $_POST['gardasil_given_date'], 'type' => 'date'],
            'Next Dose Due' => ['value' => $_POST['gardasil_next_date'], 'type' => 'date'],
            'Notes' => ['value' => $_POST['gardasil_notes'], 'type' => 'textarea'],
        ];
        break;

    case 'ob_delivery':
        $fieldData = [
            'Delivery Date' => ['value' => $_POST['ob_delivery_date'], 'type' => 'date'],
            'Pregnancy Outcome' => ['value' => $_POST['ob_delivery_outcome'], 'type' => 'select'],
            'Gender of Baby' => ['value' => $_POST['ob_delivery_gender'], 'type' => 'select'],
            'Birth Weight' => ['value' => $_POST['ob_delivery_weight'], 'type' => 'text'],
            'Notes' => ['value' => $_POST['ob_delivery_notes'], 'type' => 'textarea'],
        ];
        break;

    default:
        die("Error: Unknown service type.");
}

//Insert field data into the fields table
foreach ($fieldData as $fieldName => $fieldInfo) {
    $fieldValue = $fieldInfo['value'];
    $fieldType = $fieldInfo['type'];

    if ($fieldValue !== null && $fieldValue !== '') {
        //Check if the field already exists for the service
        $query = "SELECT field_id FROM fields WHERE service_id = ? AND field_name = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $serviceId, $fieldName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $field = $result->fetch_assoc();
            $fieldId = $field['field_id'];
        } else {
            //Insert new field if it doesn't exist
            $insertQuery = "INSERT INTO fields (service_id, field_name, field_type) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("iss", $serviceId, $fieldName, $fieldType);
            $stmt->execute();
            $fieldId = $stmt->insert_id;
        }

        //Insert patient service record
        $patient_id = isset($_GET['viewid']) ? intval($_GET['viewid']) : null;
        $dateServed = date('Y-m-d'); // Current date

        //Check if patient_id is valid before proceeding
        if ($patient_id > 0) {
            $insertRecordQuery = "INSERT INTO patient_service_records (patient_record_id, service_id, field_id, field_value, date_served) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertRecordQuery);
            $stmt->bind_param("iisss", $patient_id, $serviceId, $fieldId, $fieldValue, $dateServed);
            $stmt->execute();
        } else {
            die("Error: Invalid patient ID.");
        }
    }
}

header("Location: view_patient.php?viewid=" . $patient_id);
exit();
