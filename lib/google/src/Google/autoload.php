<?php


function google_api_php_client_autoload($className)
{
  $classPath = explode('_', $className);
  if ($classPath[0] != 'Google') {
    return;
  }
    $classPath = array_slice($classPath, 1, 2);
  $filePath = dirname(__FILE__) . '/' . implode('/', $classPath) . '.php';
  if (file_exists($filePath)) {
    require_once($filePath);
  }
}
spl_autoload_register('google_api_php_client_autoload');
