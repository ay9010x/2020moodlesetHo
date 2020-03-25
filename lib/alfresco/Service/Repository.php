<?php


require_once $CFG->libdir.'/alfresco/Service/WebService/WebServiceFactory.php';
require_once $CFG->libdir.'/alfresco/Service/BaseObject.php';

if (isset($_SESSION) == false)
{
      session_start();
}   
 
class Alfresco_Repository extends BaseObject
{
	private $_connectionUrl;	
	private $_host;
	private $_port;

	public function __construct($connectionUrl="http://localhost:8080/alfresco/api")
	{
		$this->_connectionUrl = $connectionUrl;			
		$parts = parse_url($connectionUrl);
		$this->_host = $parts["host"];
		if (empty($parts["port"])) {
			$this->_port = 80;
		} else {
			$this->_port = $parts["port"];
		}
	}
	
	public function getConnectionUrl()
	{
		return $this->_connectionUrl;
	}
	
	public function getHost()
	{
		return $this->_host;	
	}
	
	public function getPort()
	{
		return $this->_port;
	}
	
	public function authenticate($userName, $password)
	{
				
		$authenticationService = WebServiceFactory::getAuthenticationService($this->_connectionUrl);
		$result = $authenticationService->startSession(array (
			"username" => $userName,
			"password" => $password
		));
		
				$ticket = $result->startSessionReturn->ticket;
		$sessionId = $result->startSessionReturn->sessionid;
		
				if ($sessionId != null)
		{
         $sessionIds = null;
         if (isset($_SESSION["sessionIds"]) == false)
			{
            $sessionIds = array();
         }
         else
         {
            $sessionIds = $_SESSION["sessionIds"];
         }
         $sessionIds[$ticket] = $sessionId;
         $_SESSION["sessionIds"] = $sessionIds;
		}
		
		return $ticket;
	}
	
	public function createSession($ticket=null)
	{
		$session = null;
		
		if ($ticket == null)
		{
					}	
		else
		{
						
						$session = new Session($this, $ticket);	
		}
		
		return $session;
	}
	
	
	public static function getSessionId($ticket)
	{
      $result = null;
      if (isset($_SESSION["sessionIds"]) == true)
      {
         $result = $_SESSION["sessionIds"][$ticket];
      }
      return $result;

	}

}
 
 ?>