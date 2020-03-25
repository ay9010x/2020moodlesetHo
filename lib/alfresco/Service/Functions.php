<?php

 
 require_once($CFG->libdir."/alfresco/Service/Repository.php");
 require_once($CFG->libdir."/alfresco/Service/Session.php");
 
 
 function upload_file($session, $filePath, $mimetype=null, $encoding=null)
 {
 	$result = null;
 	
 	 	if (file_exists($filePath) == false)
 	{
 		throw new Exception("The file ".$filePath."does no exist.");
 	}
 	
		$fileName = basename($filePath);
	$fileSize = filesize($filePath);
	  	
		$host = $session->repository->host; 
	$port = $session->repository->port;
 	
		$address = gethostbyname($host);
	
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	if ($socket === false) 
	{
	    throw new Exception ("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
	} 	
	
		$result = socket_connect($socket, $address, $port);
	if ($result === false) 
	{
	    throw new Exception("socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)));
	} 
	
		$url = "/alfresco/upload/".urlencode($fileName)."?ticket=".$session->ticket;
	if ($mimetype != null)
	{
				$url .= "&mimetype=".$mimetype;
	}
	if ($encoding != null)
	{
				$url .= "&encoding=".$encoding;
	}
	$in = "PUT ".$url." HTTP/1.1\r\n".
	              "Content-Length: ".$fileSize."\r\n".
	              "Host: ".$address.":".$port."\r\n".
	              "Connection: Keep-Alive\r\n".
	              "\r\n";		
	socket_write($socket, $in, strlen($in));
	
		$handle = fopen($filePath, "r");
	while (feof($handle) == false)
	{
		$content = fread($handle, 1024);
		socket_write($socket, $content, strlen($content));		
	}		
	fclose($handle);
	
		$recv = socket_read ($socket, 2048, PHP_BINARY_READ);
	$index = strpos($recv, "contentUrl");
	if ($index !== false)
	{
		$result = substr($recv, $index);	
	}
	
		socket_close($socket);
		
	return $result;
 } 
?>
