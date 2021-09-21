<?php
require_once __DIR__ . "/../server/database.php";
header("Content-Type: application/json");

if(!isset($_SESSION['username'])){
    die(json_encode(["status" => "FAILED", "error" => "auth", "message" => "Your session has expired, please login and try again."], true));
}

$q = $db->prepare("SELECT `exit_time`, `reference_number`, `first_name`, `last_name`, `phone_number`, `entry_time` FROM `entries` ORDER BY `exit_time` ASC LIMIT 20");

if(!empty($_POST['time_frame_1']) || !empty($_POST['time_frame_2'])){
    if(!empty($_POST['time_frame_2'])){
        $q = $db->prepare("SELECT `exit_time`, `reference_number`, `first_name`, `last_name`, `phone_number`, `entry_time` FROM `entries` WHERE `entry_time` < :after ORDER BY `exit_time` ASC");
        $q->execute([
            "after" => brisbaneToUTC($_POST['time_frame_2'])
        ]);
        $people = $q->fetchAll(PDO::FETCH_ASSOC);
    }
    if(!empty($_POST['time_frame_1'])){
        $q = $db->prepare("SELECT `exit_time`, `reference_number`, `first_name`, `last_name`, `phone_number`, `entry_time` FROM `entries` WHERE `entry_time` > :before ORDER BY `exit_time` ASC");
        $q->execute([
            "before" => brisbaneToUTC($_POST['time_frame_1'])
        ]);
        $people = $q->fetchAll(PDO::FETCH_ASSOC);
    }
    if(!empty($_POST['time_frame_2']) && !empty($_POST['time_frame_1'])){
        $q = $db->prepare("SELECT `exit_time`, `reference_number`, `first_name`, `last_name`, `phone_number`, `entry_time` FROM `entries` WHERE `entry_time` > :before AND `entry_time` < :after ORDER BY `exit_time` ASC");
        $q->execute([
            "before" => brisbaneToUTC($_POST['time_frame_1']),
            "after" => brisbaneToUTC($_POST['time_frame_2'])
        ]);
        $people = $q->fetchAll(PDO::FETCH_ASSOC);
    }
}

if(!isset($people)){
    $q->execute();
    $people = $q->fetchAll(PDO::FETCH_ASSOC);
}
$table = "";

foreach($people as $person){
    $table .= "<tr>";
    $table .= "<td>" . $person['reference_number'] . "</td>";
    $table .= "<td>" . $person['first_name'] . "</td>";
    $table .= "<td>" . $person['last_name'] . "</td>";
    $table .= "<td>" . $person['phone_number'] . "</td>";
    $table .= "<td>" . utcToBrisbane($person['entry_time']) . " GMT+10</td>";

    if($person['exit_time'] == "0000-00-00 00:00:00"){
        $table .= "<td><input type=\"button\" class=\"mark_as_exited_btn\" data-refno=\"" . $person['reference_number'] . "\" value=\"Mark as Exited\"></td>";
    } else {
        $table .= "<td>Already Left</td>";
    }

    $table .= "</tr>";
}
echo json_encode(["status" => "OK", "data" => $table]);