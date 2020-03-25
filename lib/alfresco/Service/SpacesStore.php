<?php

 
require_once $CFG->libdir.'/alfresco/Service/Store.php';
require_once $CFG->libdir.'/alfresco/Service/Node.php';

class SpacesStore extends Store
{
	private $_companyHome;

	public function __construct($session)
	{
		parent::__construct($session, "SpacesStore");
	}

	public function __toString()
	{
		return $this->scheme . "://" . $this->address;
	}
	
	public function getCompanyHome()
	{
		if ($this->_companyHome == null)
		{
			$nodes = $this->_session->query($this, 'PATH:"app:company_home"');
	        $this->_companyHome = $nodes[0];
		}
		return $this->_companyHome;
	}
}
?>