<?php

$router->add_route("/dev", function() {
    $ch = curl_init('REDACTED');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, [
        "Content-type: application/x-www-form-urlencoded",
        "Origin: REDACTED",
        "Referer: REDACTED"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "username=REDACTED&password=REDACTED");
    $result = curl_exec($ch);
    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);        // get cookie
    $cookies = array();
    foreach($matches[1] as $item) {
        parse_str($item, $cookie);
        $cookies = array_merge($cookies, $cookie);
    }
    return var_export($cookies);
    
    phpinfo();
});