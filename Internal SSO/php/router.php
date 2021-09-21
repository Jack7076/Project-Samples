<?php

Class Router {

    private $routes = [];
    private $validation_flags = [];
    private $route = "";

    public function __construct(){
        // Configure / Setup Cookie
        ini_set('session.name', "proxy_authentication");

        $currentCookieParams = session_get_cookie_params();
        session_start();
    }

    public function add_route($path, $callback){
        $path = trim($path, "/");
        $this->routes[$path] = $callback;
    }

    public function handle(){
        $this->route = $_SERVER['REQUEST_URI'];
        echo $this->resolve();
        exit();
    }

    private function validate_request(){

        $valid = true;

        // Ensure that any request for authentication is encrypted when reaching the proxy system.
        if(!isset($_SERVER['HTTP_X_FORWARDED_PROTO'])){
            // If we cannot determine the use of SSL reject the request.
            $valid = false;
            $this->validation_flags["unknown_proto"] = true;
            return $valid;
        }
        if($_SERVER['HTTP_X_FORWARDED_PROTO'] != "https"){
            // If the request is not encrypted using SSL at a minimum reject the request.
            $valid = false;
            $this->validation_flags["insecure"] = true;
        }
        // Check to see if the request is coming from the Proxy.
        if($_SERVER['REMOTE_ADDR'] != "REDACTED"){
            // If the request is not coming from the proxy reject the request.
            $valid = false;
            $this->validation_flags["untrusted_proxy"] = true;
        }
        
        // Ensure request is on correct domain.
        if(substr($_SERVER['REQUEST_URI'], 0, 9) != "/api/sso/"){
            if($_SERVER['HTTP_HOST'] != "REDACTED"){
                if(isset($_SESSION['unauth'])){
                    unset($_SESSION['unauth']);
                    // Redirect the route to display an unauthorized message.
                    $this->route = "/error/unauthorized";
                } else {
                    $_SESSION['success_target'] = $_SERVER['HTTP_X_FORWARDED_PROTO'] . "://" . $_SERVER['HTTP_X_FORWARDED_HOST'] . $_SERVER['REQUEST_URI'];
                    http_response_code(307);
                    header("location: REDACTED");
                    exit();
                }
            }
        }

        return $valid;
    }

    private function resolve(){
        // If the request is not valid, stop processing.
        if(!$this->validate_request()){
            if(isset($this->routes["error/invalid"])){
                return call_user_func($this->routes["error/invalid"], [$this->validation_flags]);
            } else {
                return "Request could not be validated.";
            }
        }

        $this->route = trim($this->route, "/");

        // Check if request has a route
        if(!isset($this->routes[$this->route])){
            http_response_code(404);
            // Check if a 404 is configured
            if(isset($this->routes["error/404"])){
                return call_user_func($this->routes["error/404"]);
            } else {
                return "The specified route has not been configured.";
            }
        }

        $callback = $this->routes[$this->route];

        return call_user_func($callback);
    }

    public function process_view($view){
        ob_start();
        require_once(__DIR__ . "/../views/" . $view . ".php");
        $view = ob_get_contents();
        ob_end_clean();
        return $view;
    }

}