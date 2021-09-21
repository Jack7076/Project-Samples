<?php

$router->add_route("/logout", function() {
    global $router;

    session_unset();
    session_destroy();

    return $router->process_view("logged_out");
});

$router->add_route("/login", function() {
    global $router;

    if(isset($_POST['username'])){
        // Process Login

        $response = [];
        $response['authed'] = false;
        header("Content-type: application/json");

        $db = new Database();
        $q = $db->db->prepare("SELECT `ID`, `username`, `password`, `authentications_since_hash_change` as `ashc` FROM `users` WHERE `username` = :user OR `email` = :user");
        $q->execute([
            "user" => $_POST['username']
        ]);

        $user = $q->fetch(PDO::FETCH_ASSOC);

        if(empty($user)){
            $response['error'] = "Invalid Credentials";
            return json_encode($response);
        }

        if(password_verify($_POST['password'], $user['password'])){
            $response['error'] = false;
            $response['authed'] = true;
            if(password_needs_rehash($user['password'], PASSWORD_BCRYPT, APPLICATION_HASH_STANDARD) || $user['ashc'] >= APPLICATION_HASH_CHANGE){
                $q = $db->db->prepare("UPDATE `users` SET `password` = :updated_password, `authentications_since_hash_change` = :ashc WHERE `ID` = :u_id");
                $q->execute([
                    "updated_password" => password_hash($_POST['password'], PASSWORD_BCRYPT, APPLICATION_HASH_STANDARD),
                    "u_id" => $user['ID'],
                    "ashc" => 0
                ]);
            } else {
                $q = $db->db->prepare("UPDATE `users` SET `authentications_since_hash_change` = :ashc WHERE `ID` = :u_id");
                $ashc = $user['ashc'];
                $ashc++;
                $q->execute([
                    "u_id" => $user['ID'],
                    "ashc" => $ashc
                ]);
            }

            $_SESSION['username'] = $user['username'];
            $_SESSION['uid'] = $user['ID'];

            $response['session'] = $_SESSION;

            if(isset($_SESSION['success_target'])){
                $response["location"] = $_SESSION['success_target'];
            } else {
                $response["location"] = "REDACTED";
            }
        } else {
            $response['error'] = "Invalid Credentials";
        }

        
        
        return json_encode($response);
    }

    return $router->process_view("login");
});

$router->add_route("update_password", function() {
    header("Content-type: application/json");

    $db = new Database();

    if($db->check_credentials($_SESSION['username'], $_POST['cp'])){
        $q = $db->db->prepare("UPDATE `users` SET `password` = :updated_password, `authentications_since_hash_change` = :ashc WHERE `ID` = :u_id");
        $q->execute([
            "updated_password" => password_hash($_POST['np'], PASSWORD_BCRYPT, APPLICATION_HASH_STANDARD),
            "u_id" => $_SESSION['uid'],
            "ashc" => 0
        ]);
        return json_encode(["error" => false]);
    } else {
        return json_encode(["error" => "Invalid current password."]);
    }

    return json_encode(["error" => "It's Just Fucked"]);
});