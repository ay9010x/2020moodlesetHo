<?php



class Less_Environment{

									public $currentFileInfo;				
	public $importMultiple = false; 		

	
	public $frames = array();

	
	public $mediaBlocks = array();

	
	public $mediaPath = array();

	public static $parensStack = 0;

	public static $tabLevel = 0;

	public static $lastRule = false;

	public static $_outputMap;

	public static $mixin_stack = 0;

	
	public $functions = array();


	public function Init(){

		self::$parensStack = 0;
		self::$tabLevel = 0;
		self::$lastRule = false;
		self::$mixin_stack = 0;

		if( Less_Parser::$options['compress'] ){

			Less_Environment::$_outputMap = array(
				','	=> ',',
				': ' => ':',
				''  => '',
				' ' => ' ',
				':' => ' :',
				'+' => '+',
				'~' => '~',
				'>' => '>',
				'|' => '|',
		        '^' => '^',
		        '^^' => '^^'
			);

		}else{

			Less_Environment::$_outputMap = array(
				','	=> ', ',
				': ' => ': ',
				''  => '',
				' ' => ' ',
				':' => ' :',
				'+' => ' + ',
				'~' => ' ~ ',
				'>' => ' > ',
				'|' => '|',
		        '^' => ' ^ ',
		        '^^' => ' ^^ '
			);

		}
	}


	public function copyEvalEnv($frames = array() ){
		$new_env = new Less_Environment();
		$new_env->frames = $frames;
		return $new_env;
	}


	public static function isMathOn(){
		return !Less_Parser::$options['strictMath'] || Less_Environment::$parensStack;
	}

	public static function isPathRelative($path){
		return !preg_match('/^(?:[a-z-]+:|\/)/',$path);
	}


	
	public static function normalizePath($path){

		$segments = explode('/',$path);
		$segments = array_reverse($segments);

		$path = array();
		$path_len = 0;

		while( $segments ){
			$segment = array_pop($segments);
			switch( $segment ) {

				case '.':
				break;

				case '..':
					if( !$path_len || ( $path[$path_len-1] === '..') ){
						$path[] = $segment;
						$path_len++;
					}else{
						array_pop($path);
						$path_len--;
					}
				break;

				default:
					$path[] = $segment;
					$path_len++;
				break;
			}
		}

		return implode('/',$path);
	}


	public function unshiftFrame($frame){
		array_unshift($this->frames, $frame);
	}

	public function shiftFrame(){
		return array_shift($this->frames);
	}

}
