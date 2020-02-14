<?php



class Association extends BaseObject
{
	private $_from;
	private $_to;
	private $_type;
	
	public function __construct($from, $to, $type)
	{
		$this->_from = $from;
		$this->_to = $to;
		$this->_type = $type;	
	}
	
	public function getFrom()
	{
		return $this->_from;
	}
	
	public function getTo()
	{
		return $this->_to;
	}
	
	public function getType()
	{
		return $this->_type;
	}
}

?>
