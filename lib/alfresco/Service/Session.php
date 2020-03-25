<?php

 
require_once $CFG->libdir.'/alfresco/Service/Store.php';
require_once $CFG->libdir.'/alfresco/Service/Node.php';
require_once $CFG->libdir.'/alfresco/Service/WebService/WebServiceFactory.php';

class Session extends BaseObject
{
	public $authenticationService;
	public $repositoryService;
	public $contentService;

	private $_repository;
	private $_ticket;
	private $_stores;
	private $_namespaceMap;
	
	private $nodeCache;
	private $idCount = 0;

    
	public function __construct($repository, $ticket)  
	{
		$this->nodeCache = array();
		
		$this->_repository = $repository;
		$this->_ticket = $ticket;
		
		$this->repositoryService = WebServiceFactory::getRepositoryService($this->_repository->connectionUrl, $this->_ticket);
		$this->contentService = WebServiceFactory::getContentService($this->_repository->connectionUrl, $this->_ticket);
	}
	
	
	public function createStore($address, $scheme="workspace")
	{
				$result = $this->repositoryService->createStore(array(
													"scheme" => $scheme,
													"address" => $address));
		$store = new Store($this, $result->createStoreReturn->address, $result->createStoreReturn->scheme);											
		
				if (isset($this->_stores) == true)
		{
			$this->_stores[] = $store;
		}	
		
				return $store;
	}
	
	
	public function getStore($address, $scheme="workspace")
	{
		return new Store($this, $address, $scheme);	
	}
	
	
	public function getStoreFromString($value)
	{
		list($scheme, $address) = explode("://", $value);
    	return new Store($this, $address, $scheme);		
	}	
	
	public function getNode($store, $id)
    {
    	$node = $this->getNodeImpl($store, $id);
    	if ($node == null)
    	{
    		$node = new Node($this, $store, $id);
    		$this->addNode($node);
    	}		
    	return $node;
    }
    
    public function getNodeFromString($value)
    {
    	    	throw new Exception("getNode($value) not yet implemented");
    }
    
    
	public function addNode($node)
	{
		$this->nodeCache[$node->__toString()] = $node;
	}
	
	private function getNodeImpl($store, $id)
	{		
		$result = null;
		$nodeRef = $store->scheme . "://" . $store->address . "/" . $id;
		if (array_key_exists($nodeRef, $this->nodeCache) == true)
		{
			$result = $this->nodeCache[$nodeRef];
		}
		return $result;
	}

	
	public function save($debug=false)
	{
				$statements = array();
		foreach ($this->nodeCache as $node)
		{
			$node->onBeforeSave($statements);
		}
		
		if ($debug == true)
		{
			var_dump($statements);
			echo ("<br><br>");
		}
		
		if (count($statements) > 0)
		{
						$result = $this->repositoryService->update(array("statements" => $statements));
								
						foreach ($this->nodeCache as $node)
			{
				$node->onAfterSave($this->getIdMap($result));
			}
		}
	}
	
	
	public function clear()
	{
				$this->nodeCache = array();	
	}
	
	private function getIdMap($result)
	{
		$return = array();
		$statements = $result->updateReturn;
		if (is_array($statements) == true)
		{
			foreach ($statements as $statement)
			{
				if ($statement->statement == "create")
				{
					$id = $statement->sourceId;
					$uuid = $statement->destination->uuid;
					$return[$id] = $uuid;
				}
			}	
		}	
		else
		{
			if ($statements->statement == "create")
				{
					$id = $statements->sourceId;
					$uuid = $statements->destination->uuid;
					$return[$id] = $uuid;
				}	
		}	
		return $return;	
	}
	
	public function query($store, $query, $language='lucene')
	{
				$result = $this->repositoryService->query(array(
					"store" => $store->__toArray(),
					"query" => array(
						"language" => $language,
						"statement" => $query),
					"includeMetaData" => false));					
				
				$resultSet = $result->queryReturn->resultSet;		
		return $this->resultSetToNodes($this, $store, $resultSet);
	}

	public function getTicket()
	{
		return $this->_ticket;
	}

	public function getRepository()
	{
		return $this->_repository;
	}
	
	public function getNamespaceMap()
	{
		if ($this->_namespaceMap == null)
		{
			$this->_namespaceMap = new NamespaceMap();
		}
		return $this->_namespaceMap;
	}

	public function getStores()
	{
		if (isset ($this->_stores) == false)
		{
			$this->_stores = array ();
			$results = $this->repositoryService->getStores();

			foreach ($results->getStoresReturn as $result)
			{
				$this->_stores[] = new Store($this, $result->address, $result->scheme);
			}
		}

		return $this->_stores;
	}
	
	
	
	public function nextSessionId()
	{
		$sessionId = "session".$this->_ticket.$this->idCount;
		$this->idCount ++;
		return $sessionId;
	}
}
?>