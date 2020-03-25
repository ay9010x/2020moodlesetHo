<?php


@error_reporting(E_ALL ^ E_NOTICE);
$config = array();

require_once(dirname(__FILE__) . "/../classes/utils/Logger.php");
require_once(dirname(__FILE__) . "/../classes/utils/JSON.php");
require_once(dirname(__FILE__) . "/../config.php");
require_once(dirname(__FILE__) . "/../classes/SpellChecker.php");

if (isset($config['general.engine']))
	require_once(dirname(__FILE__) . "/../classes/" . $config["general.engine"] . ".php");


function getRequestParam($name, $default_value = false) {
	if (!isset($_REQUEST[$name]))
		return $default_value;

	if (is_array($_REQUEST[$name])) {
		$newarray = array();

		foreach ($_REQUEST[$name] as $name => $value)
			$newarray[$name] = $value;

		return $newarray;
	}

	return $_REQUEST[$name];
}

function &getLogger() {
	global $mcLogger, $man;

	if (isset($man))
		$mcLogger = $man->getLogger();

	if (!$mcLogger) {
		$mcLogger = new Moxiecode_Logger();

				$mcLogger->setPath(dirname(__FILE__) . "/../logs");
		$mcLogger->setMaxSize("100kb");
		$mcLogger->setMaxFiles("10");
		$mcLogger->setFormat("{time} - {message}");
	}

	return $mcLogger;
}

function debug($msg) {
	$args = func_get_args();

	$log = getLogger();
	$log->debug(implode(', ', $args));
}

function info($msg) {
	$args = func_get_args();

	$log = getLogger();
	$log->info(implode(', ', $args));
}

function xx_error($msg) { 	$args = func_get_args();

	$log = getLogger();
	$log->error(implode(', ', $args));
}

function warn($msg) {
	$args = func_get_args();

	$log = getLogger();
	$log->warn(implode(', ', $args));
}

function fatal($msg) {
	$args = func_get_args();

	$log = getLogger();
	$log->fatal(implode(', ', $args));
}

?>