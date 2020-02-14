<?php

namespace Box\Spout\Autoloader;

require_once 'Psr4Autoloader.php';


$srcBaseDirectory = dirname(dirname(__FILE__));

$loader = new Psr4Autoloader();
$loader->register();
$loader->addNamespace('Box\Spout', $srcBaseDirectory);
