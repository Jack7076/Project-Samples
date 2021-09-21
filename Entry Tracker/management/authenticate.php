<?php
require_once __DIR__ . "/../server/database.php";
header("Content-Type: application/json");
if(!isset($_POST['token']) || !isset($_POST['username']) || !isset($_POST['password'])){
    die(json_encode(["status" => "FAILED", "error" => "Invalid Request", "message" => "There was an error processing your request. Please try again."], true));
}
if(!verifyCaptcha($_POST['token'])){
    die(json_encode(["status" => "FAILED", "error" => "Robot Verification", "message" => "We could not verify your request. Please try again."], true));
}

$q = $db->prepare("SELECT * FROM `admins` WHERE `username` = :username AND `password` = :password LIMIT 1");
$q->execute([
    "username" => $_POST['username'],
    "password" => hash_hmac("sha512", $_POST['password'], $hmac)
]);
$res = $q->fetch(PDO::FETCH_ASSOC);
if($res != false){
    $_SESSION['username'] = $res['username'];
    $_SESSION['password'] = $res['password'];
    die(json_encode(["status" => "OK"], true));
}

die(json_encode(["status" => "FAILED", "error" => "no account", "message" => "The details you provided to seem to match any accounts in our database; double check your username and password then try again."], true));