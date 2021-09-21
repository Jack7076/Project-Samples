<?php

$router->add_route("/error/404", function() {
    //$out = "<pre>" . var_export($uri, true) . "</pre>";
    return "Custom 404 Soft-Fail Function";
});

$router->add_route("/error/invalid", function($flags) {
    $out = "<pre>" . var_export($flags, true) . "</pre>";
    return "Custom 403 Soft-Fail Function\n" . $out;
});

$router->add_route("/error/unauthorized", function() {
    global $router;
    return $router->process_view("unauthorized");
});