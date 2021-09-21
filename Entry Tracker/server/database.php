<?php
session_start();
$db = new PDO("mysql:host=localhost;dbname=entrytracker", "root", "");
$hmac = "0pBVn8%m7S!ReA^k7&9I5IECKGZteO6*lZLutbOpk0lmeDIrKFu";


if(isset($_SERVER['HTTP_CF_CONNECTING_IP'])){
    $_SERVER["REMOTE_ADDR"] = $_SERVER['HTTP_CF_CONNECTING_IP'];
}

function verifyCaptcha($token){
    $result = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, stream_context_create([
        "http" => [
            "method" => "POST",
            "header" => "Content-Type: application/x-www-form-urlencoded",
            "content" => http_build_query([
                "secret" => "REDACTED",
                "response" => $token
            ])
        ]
    ]));
    $result = json_decode($result, true);
    if($result['success']){
        return true;
    }
    return false;
}

function addEntry($first_name, $last_name, $phone_number, $marketing, $email_address = "", $physical_address = "", $address_id = "", $postal_code = ""){
    global $db;
    $q = $db->prepare("INSERT INTO `entries` (`marketing`, `reference_number`, `first_name`, `last_name`, `email`, `address`, `address_id`, `phone_number`, `entry_time`, `postal_code`) 
    VALUES (:marketing, :reference_number, :first_name, :last_name, :email, :address, :address_id, :phone_number, :entry_time, :postal_code)");
    
    if(!filter_var($email_address, FILTER_VALIDATE_EMAIL)){
        if(empty($physical_address) || empty($address_id)){
            return false;
        }
    }


    // Reference Number ->
    // DATE YmdHi -> + First 10 Chars of SHA1 of first name, last name, phone number and time.
    $utcTime = gmdate("Y-m-d H:i:s");
    $brisbaneTime = utcToBrisbane($utcTime);
    $hash = hash("sha1", $first_name . $last_name . $phone_number . $brisbaneTime);
    $hash = strtoupper(substr($hash, 0, 10));
    $reference = date("ymd", strtotime($brisbaneTime)) . $hash;

    $q->execute([
        "first_name" => $first_name,
        "last_name" => $last_name,
        "email" => $email_address,
        "address" => $physical_address,
        "address_id" => $address_id,
        "phone_number" => $phone_number,
        "entry_time" => $utcTime,
        "reference_number" => $reference,
        "postal_code" => $postal_code,
        "marketing" => ($marketing == "true") ? 1:0
    ]);

    return $reference;
}

function setExitTime($reference_number){
    global $db;
    $q = $db->prepare("SELECT `exit_time` FROM `entries` WHERE `reference_number` = :ref_no LIMIT 1");
    $q->execute([
        "ref_no" => $reference_number
    ]);
    $res = $q->fetch(PDO::FETCH_ASSOC);
    if($res !== false){
        if($res['exit_time'] == "0000-00-00 00:00:00"){
            $q = $db->prepare("UPDATE `entries` SET `exit_time` = :time WHERE `reference_number` = :ref_no");
            $q->execute([
                "time" => gmdate("Y-m-d H:i:s"),
                "ref_no" => $reference_number
            ]);
            return true;
        }
    }
    return false;
}

function utcToBrisbane($timestamp) {
    $reference = new DateTime($timestamp, new DateTimeZone('UTC'));
    $reference->setTimezone(new DateTimeZone('Australia/Brisbane'));
    return $reference->format("Y-m-d H:i:s");
}

function brisbaneToUTC($timestamp) {
    $reference = new DateTime($timestamp, new DateTimeZone('Australia/Brisbane'));
    $reference->setTimezone(new DateTimeZone('UTC'));
    return $reference->format("Y-m-d H:i:s");
}