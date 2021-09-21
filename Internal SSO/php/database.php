<?php

define("APPLICATION_HASH_STANDARD", ["cost" => 14]);
define("APPLICATION_HASH_CHANGE", 1);

date_default_timezone_set("Australia/Brisbane");

class Database {
    
    public $db;

    public function __construct() {
        $this->db = new PDO("mysql:host=REDACTED;dbname=REDACTED",
                       "sso_user", "REDACTED",
                       [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    }

    public function check_credentials($username, $password){
        $q = $this->db->prepare("SELECT `ID`, `username`, `password`, `authentications_since_hash_change` as `ashc` FROM `users` WHERE `username` = :user OR `email` = :user");
        $q->execute([
            "user" => $username
        ]);

        $user = $q->fetch(PDO::FETCH_ASSOC);

        if(empty($user)){
            return false;
        }

        $valid = false;

        if(password_verify($password, $user['password'])){
            $valid = true;
            if(password_needs_rehash($user['password'], PASSWORD_BCRYPT, APPLICATION_HASH_STANDARD) || $user['ashc'] >= APPLICATION_HASH_CHANGE){
                $q = $this->db->prepare("UPDATE `users` SET `password` = :updated_password, `authentications_since_hash_change` = :ashc WHERE `ID` = :u_id");
                $q->execute([
                    "updated_password" => password_hash($password, PASSWORD_BCRYPT, APPLICATION_HASH_STANDARD),
                    "u_id" => $user['ID'],
                    "ashc" => 0
                ]);
            } else {
                $q = $this->db->prepare("UPDATE `users` SET `authentications_since_hash_change` = :ashc WHERE `ID` = :u_id");
                $ashc = $user['ashc'];
                $ashc++;
                $q->execute([
                    "u_id" => $user['ID'],
                    "ashc" => $ashc
                ]);
            }
        }
        return $valid;
    }
}