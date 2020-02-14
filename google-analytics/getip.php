<?php
function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP')) {
        $ipaddress = getenv('HTTP_CLIENT_IP');
    }
    else if(getenv('HTTP_X_FORWARDED_FOR')) {
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    }
    else if(getenv('HTTP_X_FORWARDED')) {
        $ipaddress = getenv('HTTP_X_FORWARDED');
    }
    else if(getenv('HTTP_FORWARDED_FOR')) {
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    }
    else if(getenv('HTTP_FORWARDED')) {
       $ipaddress = getenv('HTTP_FORWARDED');
    }
    else if(getenv('REMOTE_ADDR')) {
        $ipaddress = getenv('REMOTE_ADDR');
    }
    else {
        $ipaddress = 'UNKNOWN';
    }
    
    if ($ipaddress === "::1") {
        $ipaddress = "127.0.0.1";
    }
    
    return $ipaddress;
}

if (isset($_GET["callback"])) {
    header('Content-Type: application/javascript');
    echo $_GET["callback"] . '("' . get_client_ip() . '");';
}
else {
    header('Content-Type: text/plain');
    echo get_client_ip();
}
