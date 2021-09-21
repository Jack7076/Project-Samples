<?php
error_reporting(E_ALL);
require_once __DIR__ . "/server/database.php";
header("Content-Type: application/json");

if(!isset($_POST['token']) || !isset($_POST['form']) || !isset($_POST["postal_address"]) || !isset($_POST["postal_id"]) || !isset($_POST['marketing'])){
    die(json_encode(["status" => "FAILED", "error" => "Invalid Request", "message" => "There was an error processing your request. Please try again."], true));
}
if(!verifyCaptcha($_POST['token'])){
    die(json_encode(["status" => "FAILED", "error" => "Robot Verification", "message" => "We could not verify your request. Please try again."], true));
}



if($_POST["postal_address"] == "NA" || $_POST["postal_code"] == "NA" || $_POST["postal_id"] == "NA") {
    if(empty($_POST['form']['email_address'])){
        die(json_encode(["status" => "FAILED", "error" => "Form was not completed", "message" => "There was an error processing your details, please ensure you have completed the form correctly and try again."]));
    } else {
        $reference = addEntry($_POST['form']['first_name'], $_POST['form']['last_name'], $_POST['form']['phone_number'], $_POST['marketing'], $_POST['form']['email_address']);
        if(isset($_POST['children'])){
            foreach($_POST['children'] as $child){
                addEntry($child['first_name'], $child['last_name'], $_POST['form']['phone_number'], "0", $_POST['form']['email_address']);
            }
        }
        if($reference != false){
            $_SESSION['first_name']     = $_POST['form']['first_name'];
            $_SESSION['last_name']      = $_POST['form']['last_name'];
            $_SESSION['phone_number']   = $_POST['form']['phone_number'];
            $_SESSION['email_address']  = $_POST['form']['email_address'];
            $_SESSION['current_ref']    = $reference;
            die(json_encode(["status" => "OK", "redirect" => "complete"], true));
        }
    }
} else {
    $reference = addEntry($_POST['form']['first_name'], $_POST['form']['last_name'], $_POST['form']['phone_number'], $_POST['marketing'], "", $_POST['postal_address'], $_POST['postal_id'], $_POST['postal_code']);
    if(isset($_POST['children'])){
        foreach($_POST['children'] as $child){
            addEntry($child['first_name'], $child['last_name'], $_POST['form']['phone_number'], "0", "", $_POST['postal_address'], $_POST['postal_id'], $_POST['postal_code']);
        }
    }
    if($reference != false){
        $_SESSION['first_name']     = $_POST['form']['first_name'];
        $_SESSION['last_name']      = $_POST['form']['last_name'];
        $_SESSION['phone_number']   = $_POST['form']['phone_number'];
        $_SESSION['postal_address'] = $_POST['postal_address'];
        $_SESSION['postal_id']      = $_POST['postal_id'];
        $_SESSION['postal_code']    = $_POST['postal_code'];
        $_SESSION['current_ref']    = $reference;
        die(json_encode(["status" => "OK", "redirect" => "complete"], true));
    }
}

die(json_encode(["status" => "FAILED", "error" => "Unknown", "message" => "An error occurred. Please try again."], true));