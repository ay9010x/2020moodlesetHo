<?php



class SimplePie_Cache_Memcache implements SimplePie_Cache_Base
{
	
	protected $cache;

	
	protected $options;

	
	protected $name;

	
	public function __construct($location, $name, $type)
	{
		$this->options = array(
			'host' => '127.0.0.1',
			'port' => 11211,
			'extras' => array(
				'timeout' => 3600, 				'prefix' => 'simplepie_',
			),
		);
		$parsed = SimplePie_Cache::parse_URL($location);
		$this->options['host'] = empty($parsed['host']) ? $this->options['host'] : $parsed['host'];
		$this->options['port'] = empty($parsed['port']) ? $this->options['port'] : $parsed['port'];
		$this->options['extras'] = array_merge($this->options['extras'], $parsed['extras']);
		$this->name = $this->options['extras']['prefix'] . md5("$name:$type");

		$this->cache = new Memcache();
		$this->cache->addServer($this->options['host'], (int) $this->options['port']);
	}

	
	public function save($data)
	{
		if ($data instanceof SimplePie)
		{
			$data = $data->data;
		}
		return $this->cache->set($this->name, serialize($data), MEMCACHE_COMPRESSED, (int) $this->options['extras']['timeout']);
	}

	
	public function load()
	{
		$data = $this->cache->get($this->name);

		if ($data !== false)
		{
			return unserialize($data);
		}
		return false;
	}

	
	public function mtime()
	{
		$data = $this->cache->get($this->name);

		if ($data !== false)
		{
						return time();
		}

		return false;
	}

	
	public function touch()
	{
		$data = $this->cache->get($this->name);

		if ($data !== false)
		{
			return $this->cache->set($this->name, $data, MEMCACHE_COMPRESSED, (int) $this->duration);
		}

		return false;
	}

	
	public function unlink()
	{
		return $this->cache->delete($this->name, 0);
	}
}
