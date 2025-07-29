<?php
// config.php - Ito ay i-include sa SIMULA ng lahat ng .php files

$maintenance_file = __DIR__ . '/maintenance.flag';

// Listahan ng mga pahina na pwedeng ma-access kahit nasa maintenance
$allowed_uris = [
    '../admin/index.php'
];

// Kung meron maintenance.flag AT hindi kasali sa allowed pages → redirect
if (file_exists($maintenance_file)) {
    $current_page = $_SERVER['PHP_SELF'];

    // Check kung exempted (hal. login page)
    if (!in_array($current_page, $allowed_uris)) {
        if (basename($current_page) !== 'maintenance.html') {
            header('HTTP/1.1 503 Service Unavailable');
            header('Location: maintenance.html');
            exit();
        }
    }
}
?>