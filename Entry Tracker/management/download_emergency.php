<?php
require_once __DIR__ . "/../server/database.php";

if(!isset($_SESSION['username'])){
    header("Location: login");
    die();
}
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=emergency_response_list.csv');

if(!isset($_GET['dateAfter']) || !isset($_GET['dateBefore'])){
    die();
}

$output = fopen('php://output', 'w');

fputcsv($output, array('Reference Number', 'First Name', 'Last Name',
    'Entry Time (GMT+10)', 'Exit Time (GMT+10)', 'Phone Number', 'Email Address',
    'Postal Address', 'Post Code'));

$q = $db->prepare("SELECT * FROM `entries` WHERE `entry_time` > :before AND `entry_time` < :after ORDER BY `exit_time` ASC");
$q->execute([
    "before" => brisbaneToUTC($_GET['dateAfter']),
    "after" => brisbaneToUTC($_GET['dateBefore'])
]);

$entries = $q->fetchAll(PDO::FETCH_ASSOC);

foreach($entries as $entry){
    $row = [
        $entry['reference_number'],
        $entry['first_name'],
        $entry['last_name'],
        utcToBrisbane($entry['entry_time']),
        utcToBrisbane($entry['exit_time']),
        $entry['phone_number'],
        $entry['email'],
        $entry['address'],
        $entry['postal_code']
    ];
    fputcsv($output, $row);
}