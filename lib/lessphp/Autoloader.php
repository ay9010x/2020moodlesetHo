<?php


class Less_Autoloader {

	
	protected static $registered = false;

	
	protected static $libDir;

	
	public static function register(){
		if( self::$registered ){
			return;
		}

		self::$libDir = dirname(__FILE__);

		if(false === spl_autoload_register(array('Less_Autoloader', 'loadClass'))){
			throw new Exception('Unable to register Less_Autoloader::loadClass as an autoloading method.');
		}

		self::$registered = true;
	}

	
	public static function unregister(){
		spl_autoload_unregister(array('Less_Autoloader', 'loadClass'));
		self::$registered = false;
	}

	
	public static function loadClass($className){


				if(strpos($className, 'Less_') !== 0){
			return;
		}

		$className = substr($className,5);
		$fileName = self::$libDir . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

		if(file_exists($fileName)){
			require $fileName;
			return true;
		}else{
			throw new Exception('file not loadable '.$fileName);
		}
	}

}