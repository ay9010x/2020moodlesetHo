<?php


class SpellChecker {
	
	public function __construct(&$config) {
		$this->_config = $config;
	}

    
    public function SpellChecker(&$config) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($config);
    }

	
	function &loopback() {
		return func_get_args();
	}

	
	function &checkWords($lang, $words) {
		return $words;
	}

	
	function &getSuggestions($lang, $word) {
		return array();
	}

	
	function throwError($str) {
		die('{"result":null,"id":null,"error":{"errstr":"' . addslashes($str) . '","errfile":"","errline":null,"errcontext":"","level":"FATAL"}}');
	}
}

?>
