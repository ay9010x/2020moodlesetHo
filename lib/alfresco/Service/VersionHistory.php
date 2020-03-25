<?php

 
 
 class VersionHistory extends BaseObject 
 {
 	
 	private $_node;
 	
 	
 	private $_versions;
 	
 	
 	public function __construct($node) 
	{ 
		$this->_node = $node;
		$this->populateVersionHistory();
	}
	
	
	public function getNode()
	{
		return $this->_node;
	}
	
	
	public function getVersions()
	{
		return $this->_versions;
	}
	
	
	private function populateVersionHistory()
	{
				$client = WebServiceFactory::getAuthoringService($this->_node->session->repository->connectionUrl, $this->_node->session->ticket);
		$result = $client->getVersionHistory(array("node" => $this->_node->__toArray()));
				
			}
 }
?>
