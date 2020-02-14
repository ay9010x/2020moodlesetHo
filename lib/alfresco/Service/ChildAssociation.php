<?php


class ChildAssociation extends BaseObject
{
	private $_parent;
	private $_child;
	private $_type;
	private $_name;
	private $_isPrimary;
	private $_nthSibling;
	
	public function __construct($parent, $child, $type, $name, $isPrimary=false, $nthSibling=0)
	{
		$this->_parent = $parent;
		$this->_child = $child;
		$this->_type = $type;
		$this->_name = $name;
		$this->_isPrimary = $isPrimary;
		$this->_nthSibling = $nthSibling;
	}
	
	public function getParent()
	{
		return $this->_parent;
	}
	
	public function getChild()
	{
		return $this->_child;
	}
	
	public function getType()
	{
		return $this->_type;
	}
	
	public function getName()
	{
		return $this->_name;
	}
	
	public function getIsPrimary()
	{
		return $this->_isPrimary;
	}
	
	public function getNthSibling()
	{
		return $this->_nthSibling;
	}
}
?>
