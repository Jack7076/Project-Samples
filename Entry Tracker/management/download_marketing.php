<?php
require_once __DIR__ . "/../server/database.php";

if(!isset($_SESSION['username'])){
    header("Location: login");
    die();
}
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=marketing_list.csv');

$output = fopen('php://output', 'w');

fputcsv($output, array('Reference Number', 'First Name', 'Last Name', 'Phone Number', 'Email Address'));
$q = $db->prepare("SELECT * FROM `entries` WHERE `marketing` = 1 ORDER BY `exit_time` ASC");
$q->execute();

$entries = $q->fetchAll(PDO::FETCH_ASSOC);

$emails = [];

foreach($entries as $entry){
    if(in_array($entry['email'], $emails)){
        continue;
    }
    array_push($emails, $entry['email']);
    $row = [
        $entry['reference_number'],
        $entry['first_name'],
        $entry['last_name'],
        $entry['phone_number'],
        $entry['email']
    ];
    fputcsv($output, $row);
}