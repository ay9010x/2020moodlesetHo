<?php

 
define("DEBUG", "Debug");
define("WARNING", "Warning");
define("INFO", "Information");

$debugLevel = array(DEBUG, WARNING, INFO);
$warningLevel = array(WARNING, INFO);
$infoLevel = array(INFO);
$noneLevel = array();

$defaultLogLevel = $infoLevel;

$logFile = "c:/work/AlfrescoPHPLog.txt"; 
$componentLogLevels = array(
								"integration.mediawiki.ExternalStoreAlfresco" => $debugLevel
				   			);
 
 
?>
