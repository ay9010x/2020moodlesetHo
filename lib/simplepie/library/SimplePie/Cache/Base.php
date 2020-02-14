<?php



interface SimplePie_Cache_Base
{
	
	const TYPE_FEED = 'spc';

	
	const TYPE_IMAGE = 'spi';

	
	public function __construct($location, $name, $type);

	
	public function save($data);

	
	public function load();

	
	public function mtime();

	
	public function touch();

	
	public function unlink();
}