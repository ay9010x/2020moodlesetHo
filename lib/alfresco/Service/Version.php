<?php


 
 
 class Version extends BaseObject 
 {
 	private $_session;
 	private $_store;
 	private $_id;
 	private $_description;
 	private $_major;
 	private $_properties;
 	private $_type;
 	private $_aspects;
 	
 	
 	public function __construct($session, $store, $id, $description=null, $major=false)
 	{
		$this->_session = $session;
		$this->_store = $store;
		$this->_id = $id;
		$this->_description = $description;
		$this->_major = $major; 	
		$this->_properties = null;
		$this->_aspects = null;
		$this->_type = null;	
 	}	
 	
	
	public function __get($name)
	{
		$fullName = $this->_session->namespaceMap->getFullName($name);
		if ($fullName != $name)
		{
			$this->populateProperties();	
			if (array_key_exists($fullName, $this->_properties) == true)
			{
				return $this->_properties[$fullName];
			}	
			else
			{	
				return null;	
			} 	
		}	
		else
		{
			return parent::__get($name);
		}
	}
 	
 	
 	public function getSession()
 	{
 		return $this->_session;
 	}
 	
 	
 	public function getStore()
 	{
 		return $this->_store;
 	}
 	
 	public function getId()
 	{
 		return $this->_id;
 	}
 	
 	public function getDescription()
 	{
 		return $this->_description;
 	}
 	
 	public function getMajor()
 	{
 		return $this->_major;
 	}
 	
 	public function getType()
 	{
 		return $this->_type;
 	}
 	
 	public function getProperties()
 	{
 		return $this->_properties;
 	}
 	
 	public function getAspects()
 	{
 		return $this->_aspects;
 	}
 	
 	private function populateProperties()
	{
		if ($this->_properties == null)
		{	
			$result = $this->_session->repositoryService->get(array (
					"where" => array (
						"nodes" => array(
							"store" => $this->_store->__toArray(),
							"uuid" => $this->_id))));	
							
			$this->populateFromWebServiceNode($result->getReturn);
		}	
	}
	
	private function populateFromWebServiceNode($webServiceNode)
	{
		$this->_type = $webServiceNode->type;

				$this->_aspects = array();
		$aspects = $webServiceNode->aspects;
		if (is_array($aspects) == true)
		{
			foreach ($aspects as $aspect)
			{
				$this->_aspects[] = $aspect;
			}
		}
		else
		{
			$this->_aspects[] = $aspects;	
		}		

				$this->_properties = array();
		foreach ($webServiceNode->properties as $propertyDetails) 
		{
			$name = $propertyDetails->name;
			$isMultiValue = $propertyDetails->isMultiValue;
			$value = null;
			if ($isMultiValue == false)
			{
				$value = $propertyDetails->value;
				if ($this->isContentData($value) == true)
				{
					$value = new ContentData($this, $name);
				}
			}
			else
			{
				$value = $propertyDetails->values;
			}
			
			$this->_properties[$name] = $value;
		}		
	}
 }
?>
