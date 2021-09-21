<?php

require_once __DIR__ . "/php/database.php";
require_once __DIR__ . "/php/router.php";
require_once __DIR__ . "/php/authorization_handler.php";

$router = new Router();

$router->add_route("/", function() {
    global $router;

    if(isset($_SESSION['uid']))
        return $router->process_view("dashboard");
    
    $_SESSION['success_target'] = "/";

    header("Location: /login");
    return "Redirecting ...";
});

foreach (glob(__DIR__ . "/controllers/*.php") as $controller){
    include $controller;
}

$router->handle();
