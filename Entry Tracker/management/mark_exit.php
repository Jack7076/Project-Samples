<?php
require_once __DIR__ . "/../server/database.php";
header("Content-Type: application/json");

if(!isset($_SESSION['username'])){
    die(json_encode(["status" => "FAILED", "error" => "auth", "message" => "Your session has expired, please login and try again."], true));
}

if(setExitTime($_POST['ref_no'])){
    die(json_encode(["status" => "OK"], true));
}
die(json_encode(["status" => "FAILED"], true));