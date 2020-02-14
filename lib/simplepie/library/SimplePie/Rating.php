<?php



class SimplePie_Rating
{
	
	var $scheme;

	
	var $value;

	
	public function __construct($scheme = null, $value = null)
	{
		$this->scheme = $scheme;
		$this->value = $value;
	}

	
	public function __toString()
	{
				return md5(serialize($this));
	}

	
	public function get_scheme()
	{
		if ($this->scheme !== null)
		{
			return $this->scheme;
		}
		else
		{
			return null;
		}
	}

	
	public function get_value()
	{
		if ($this->value !== null)
		{
			return $this->value;
		}
		else
		{
			return null;
		}
	}
}