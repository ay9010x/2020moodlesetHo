<?php



spl_autoload_register(array(new SimplePie_Autoloader(), 'autoload'));

if (!class_exists('SimplePie'))
{
	trigger_error('Autoloader not registered properly', E_USER_ERROR);
}


class SimplePie_Autoloader
{
	
	public function __construct()
	{
		$this->path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'library';
	}

	
	public function autoload($class)
	{
				if (strpos($class, 'SimplePie') !== 0)
		{
			return;
		}

		$filename = $this->path . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
		include $filename;
	}
}