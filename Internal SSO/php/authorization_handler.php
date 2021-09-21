<?php

Class Authorization_Handler {
    
    public $uid;
    private $db;
    private $sids;

    public function __construct(){
        $this->uid = $_SESSION['uid'];
        $this->db = new Database();
    }

    public function get_authorizations(){
        $q = $this->db->db->prepare("SELECT `ID` as `usid`, `sid`, `exp` FROM `access` WHERE `user` = :u_id");
        $q->execute([
            "u_id" => $this->uid,
        ]);
        $this->sids = $q->fetchAll();
        return $this->sids;
    }

    public function handle_sso($domain){
        return null;
    }

    public function check_authorization($sid){
        if(empty($this->sids))
            $this->get_authorizations();
        
        $authorized = false;

        foreach($this->sids as $sid_row){
            if($sid_row['sid'] == $sid && strtotime($sid_row['exp']) > strtotime("now")){
                $authorized = true;
                break;
            }
        }
        return $authorized;
    }
}