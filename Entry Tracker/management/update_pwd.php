<?php
require_once __DIR__ . "/../server/database.php";
header("Content-Type: application/json");

if(!isset($_SESSION['username'])){
    die(json_encode(["status" => "FAILED", "error" => "auth", "message" => "Your session has expired, please login and try again."], true));
}

$hashed_old_pwd = hash_hmac("sha512", $_POST['cu_pwd'], $hmac);
$hashed_new_pwd = hash_hmac("sha512", $_POST['nw_pwd'], $hmac);

if($hashed_old_pwd != $_SESSION['password']){
    die(json_encode(["status" => "FAILED", "error" => "oldpwd"]));
}

$q = $db->prepare("UPDATE `admins` SET `password` = :npwd WHERE `username` = :usrname");
$q->execute([
    "npwd" => $hashed_new_pwd,
    "usrname" => $_SESSION['username']
]);

$_SESSION['password'] = $hashed_new_pwd;


die(json_encode(["status" => "OK"]));