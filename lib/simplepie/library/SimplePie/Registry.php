<?php



class SimplePie_Registry
{
	
	protected $default = array(
		'Cache' => 'SimplePie_Cache',
		'Locator' => 'SimplePie_Locator',
		'Parser' => 'SimplePie_Parser',
		'File' => 'SimplePie_File',
		'Sanitize' => 'SimplePie_Sanitize',
		'Item' => 'SimplePie_Item',
		'Author' => 'SimplePie_Author',
		'Category' => 'SimplePie_Category',
		'Enclosure' => 'SimplePie_Enclosure',
		'Caption' => 'SimplePie_Caption',
		'Copyright' => 'SimplePie_Copyright',
		'Credit' => 'SimplePie_Credit',
		'Rating' => 'SimplePie_Rating',
		'Restriction' => 'SimplePie_Restriction',
		'Content_Type_Sniffer' => 'SimplePie_Content_Type_Sniffer',
		'Source' => 'SimplePie_Source',
		'Misc' => 'SimplePie_Misc',
		'XML_Declaration_Parser' => 'SimplePie_XML_Declaration_Parser',
		'Parse_Date' => 'SimplePie_Parse_Date',
	);

	
	protected $classes = array();

	
	protected $legacy = array();

	
	public function __construct() { }

	
	public function register($type, $class, $legacy = false)
	{
		if (!is_subclass_of($class, $this->default[$type]))
		{
			return false;
		}

		$this->classes[$type] = $class;

		if ($legacy)
		{
			$this->legacy[] = $class;
		}

		return true;
	}

	
	public function get_class($type)
	{
		if (!empty($this->classes[$type]))
		{
			return $this->classes[$type];
		}
		if (!empty($this->default[$type]))
		{
			return $this->default[$type];
		}

		return null;
	}

	
	public function &create($type, $parameters = array())
	{
		$class = $this->get_class($type);

		if (in_array($class, $this->legacy))
		{
			switch ($type)
			{
				case 'locator':
															$replacement = array($this->get_class('file'), $parameters[3], $this->get_class('content_type_sniffer'));
					array_splice($parameters, 3, 1, $replacement);
					break;
			}
		}

		if (!method_exists($class, '__construct'))
		{
			$instance = new $class;
		}
		else
		{
			$reflector = new ReflectionClass($class);
			$instance = $reflector->newInstanceArgs($parameters);
		}

		if (method_exists($instance, 'set_registry'))
		{
			$instance->set_registry($this);
		}
		return $instance;
	}

	
	public function &call($type, $method, $parameters = array())
	{
		$class = $this->get_class($type);

		if (in_array($class, $this->legacy))
		{
			switch ($type)
			{
				case 'Cache':
															if ($method === 'get_handler')
					{
						$result = @call_user_func_array(array($class, 'create'), $parameters);
						return $result;
					}
					break;
			}
		}

		$result = call_user_func_array(array($class, $method), $parameters);
		return $result;
	}
}