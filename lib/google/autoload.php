<?php


if (!defined('E_USER_DEPRECATED')) {
  define('E_USER_DEPRECATED', E_USER_WARNING);
}

$error = "google-api-php-client's autoloader was moved to src/Google/autoload.php in 1.1.3. This ";
$error .= "redirect will be removed in 1.2. Please adjust your code to use the new location.";
trigger_error($error, E_USER_DEPRECATED);
require_once dirname(__FILE__) . '/src/Google/autoload.php';
