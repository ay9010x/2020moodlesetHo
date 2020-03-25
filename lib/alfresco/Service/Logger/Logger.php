<?php

 
 require_once $CFG->libdir."/alfresco/Service/Logger/LoggerConfig.php";
 
 class Logger
 { 	
 	private $componentName;
 	
 	public function __construct($componentName = null)
 	{
 		$this->componentName = $componentName;
 	}	
 	
 	public function isDebugEnabled()
 	{
 		return $this->isEnabled(DEBUG);
 	}
 	
 	public function debug($message)
 	{
 		$this->write(DEBUG, $message);	
 	}
 	
 	public function isWarningEnabled()
 	{
 		return $this->isEnabled(WARNING);
 	}
 	
 	public function warning($message)
 	{
 		$this->write(WARNING, $message);	
 	}
 	
 	public function isInformationEnabled()
 	{
 		return $this->isEnabled(INFORMATION); 		
 	}
 	
 	public function information($message)
 	{
 		$this->write(INFORMATION, $message);	
 	}
 	
 	private function isEnabled($logLevel)
 	{
 		global $componentLogLevels, $defaultLogLevel;
 		
 		$logLevels = $defaultLogLevel;
 		if ($this->componentName != null && isset($componentLogLevels[$this->componentName]) == true)
 		{
 			$logLevels = $componentLogLevels[$this->componentName];
 		}
 		
 		return in_array($logLevel, $logLevels);
 	}
 	
 	private function write($logLevel, $message)
 	{
 		global $logFile;
 		
		$handle = fopen($logFile, "a");
		fwrite($handle, $logLevel." ".date("G:i:s d/m/Y")." - ".$message."\r\n");
		fclose($handle);
 	}
 }
?>
