<?php

$router->add_route("/api/sso/authenticate", function() {

    $requested_domain = $_SERVER['HTTP_X_FORWARDED_HOST'];
    $requested_uri = $_SERVER['HTTP_X_FORWARDED_URI'];

    $auth_details_file = fopen("logs/last_authentication_request.log", "w");
    fwrite($auth_details_file, "Server: \n");
    fwrite($auth_details_file, var_export($_SERVER, true));
    fwrite($auth_details_file, "Request: \n");
    fwrite($auth_details_file, var_export($_REQUEST, true));
    fwrite($auth_details_file, "\nPOST: \n");
    fwrite($auth_details_file, var_export($_POST, true));
    fwrite($auth_details_file, "\nGET: \n");
    fwrite($auth_details_file, var_export($_GET, true));

    // $_SESSION['success_target'] = $_SERVER['HTTP_X_FORWARDED_PROTO'] . "://" . $_SERVER['HTTP_X_FORWARDED_HOST'] . $_SERVER['REQUEST_URI'];

    // http_response_code(403);

    // Handle Special Cases - eg. API Access
    switch($requested_domain){
        case "REDACTED":
            if(str_starts_with($requested_uri, "/api/v2")){
                http_response_code(201);
                return "API Bypass";
            }
        break;
        case "REDACTED":
            if(str_starts_with($requested_uri, "/api/")){
                http_response_code(201);
                return "API Bypass";
            }
        break;
    }

    if(isset($_SESSION['uid'])){

        $db = new Database();
        $q = $db->db->prepare("SELECT `ID`, `sid`, `exp`, `user` FROM `access` WHERE `user` = :u_id AND `sid` = :r_sid AND `exp` > NOW() LIMIT 1");
        $q->execute([
            "u_id" => $_SESSION['uid'],
            "r_sid" => "domain:" . $requested_domain
        ]);

        $user_access = $q->fetch(PDO::FETCH_ASSOC);

        $auth_handler = new Authorization_Handler();

        if($auth_handler->check_authorization("global:full_access")){
            http_response_code(202);
            $auth_handler->handle_sso($requested_domain);
            return "Super Admin Authorized";
        }


        if(!empty($user_access)){
            http_response_code(200);
            $auth_handler->handle_sso($requested_domain);
            return "Authed";
        } else {
            $_SESSION['unauth'] = true;
            http_response_code(403);
            return "Unauthorized";
        }
        
    } else {
        http_response_code(307);
        header("location: REDACTED");
        return "";
    }
    http_response_code(560);
    return "";
});

$router->add_route("/api/get_user", function () {
    header("Content-type: application/json");
    if(!isset($_SESSION['uid']))
        return json_encode(["error" => "Unauthorized"]);
    
    $auth_handler = new Authorization_Handler();

    if(!$auth_handler->check_authorization("application:administrator")){
        return json_encode(["error" => "Unauthorized"]);
    }

    $db = new Database();

    $q = $db->db->prepare("SELECT `username`, `email` FROM `users` WHERE `ID` = :u_id");
    $q->execute([
        "u_id" => $_POST['uid']
    ]);

    $user = $q->fetch(PDO::FETCH_ASSOC);

    if(!empty($user)){
        return json_encode(["error" => false, "user" => $user]);
    }

    return json_encode(["error" => "Unable to access user details"]);
});