<?php



class SimplePie_Cache_File implements SimplePie_Cache_Base
{
	
	protected $location;

	
	protected $filename;

	
	protected $extension;

	
	protected $name;

	
	public function __construct($location, $name, $type)
	{
		$this->location = $location;
		$this->filename = $name;
		$this->extension = $type;
		$this->name = "$this->location/$this->filename.$this->extension";
	}

	
	public function save($data)
	{
		if (file_exists($this->name) && is_writeable($this->name) || file_exists($this->location) && is_writeable($this->location))
		{
			if ($data instanceof SimplePie)
			{
				$data = $data->data;
			}

			$data = serialize($data);
			return (bool) file_put_contents($this->name, $data);
		}
		return false;
	}

	
	public function load()
	{
		if (file_exists($this->name) && is_readable($this->name))
		{
			return unserialize(file_get_contents($this->name));
		}
		return false;
	}

	
	public function mtime()
	{
		if (file_exists($this->name))
		{
			return filemtime($this->name);
		}
		return false;
	}

	
	public function touch()
	{
		if (file_exists($this->name))
		{
			return touch($this->name);
		}
		return false;
	}

	
	public function unlink()
	{
		if (file_exists($this->name))
		{
			return unlink($this->name);
		}
		return false;
	}
}
