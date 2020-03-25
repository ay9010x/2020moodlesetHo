<?php



class SimplePie_Cache
{
	
	protected static $handlers = array(
		'mysql' => 'SimplePie_Cache_MySQL',
		'memcache' => 'SimplePie_Cache_Memcache',
	);

	
	private function __construct() { }

	
	public static function get_handler($location, $filename, $extension)
	{
		$type = explode(':', $location, 2);
		$type = $type[0];
		if (!empty(self::$handlers[$type]))
		{
			$class = self::$handlers[$type];
			return new $class($location, $filename, $extension);
		}

		return new SimplePie_Cache_File($location, $filename, $extension);
	}

	
	public function create($location, $filename, $extension)
	{
		trigger_error('Cache::create() has been replaced with Cache::get_handler(). Switch to the registry system to use this.', E_USER_DEPRECATED);
		return self::get_handler($location, $filename, $extension);
	}

	
	public static function register($type, $class)
	{
		self::$handlers[$type] = $class;
	}

	
	public static function parse_URL($url)
	{
		$params = parse_url($url);
		$params['extras'] = array();
		if (isset($params['query']))
		{
			parse_str($params['query'], $params['extras']);
		}
		return $params;
	}
}
