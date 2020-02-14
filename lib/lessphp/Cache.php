<?php

require_once( dirname(__FILE__).'/Version.php');


class Less_Cache{

		public static $cache_dir	= false;

		public static $prefix		= 'lessphp_';

		public static $prefix_vars	= 'lessphpvars_';

		public static $gc_lifetime	= 604800;


	
	public static function Get( $less_files, $parser_options = array(), $modify_vars = array() ){


				if( isset($parser_options['cache_dir']) ){
			Less_Cache::$cache_dir = $parser_options['cache_dir'];
		}

		if( empty(Less_Cache::$cache_dir) ){
			throw new Exception('cache_dir not set');
		}

		if( isset($parser_options['prefix']) ){
			Less_Cache::$prefix = $parser_options['prefix'];
		}

		if( empty(Less_Cache::$prefix) ){
			throw new Exception('prefix not set');
		}

		if( isset($parser_options['prefix_vars']) ){
			Less_Cache::$prefix_vars = $parser_options['prefix_vars'];
		}

		if( empty(Less_Cache::$prefix_vars) ){
			throw new Exception('prefix_vars not set');
		}

		self::CheckCacheDir();
		$less_files = (array)$less_files;


				if( !empty($modify_vars) ){
			$lessvars = Less_Parser::serializeVars($modify_vars);
			$vars_file = Less_Cache::$cache_dir . Less_Cache::$prefix_vars . sha1($lessvars) . '.less';

			if( !file_exists($vars_file) ){
				file_put_contents($vars_file, $lessvars);
			}

			$less_files += array($vars_file => '/');
		}


				$hash = md5(json_encode($less_files));
 		$list_file = Less_Cache::$cache_dir . Less_Cache::$prefix . $hash . '.list';

 		 		if( !isset($parser_options['use_cache']) || $parser_options['use_cache'] === true ){
			if( file_exists($list_file) ){

				self::ListFiles($list_file, $list, $cached_name);
				$compiled_name = self::CompiledName($list, $hash);

								if( !$cached_name || $cached_name === $compiled_name ){

					$output_file = self::OutputFile($compiled_name, $parser_options );

					if( $output_file && file_exists($output_file) ){
						@touch($list_file);
						return basename($output_file); 					}
				}
			}
		}

		$compiled = self::Cache( $less_files, $parser_options );
		if( !$compiled ){
			return false;
		}

		$compiled_name = self::CompiledName( $less_files, $hash );
		$output_file = self::OutputFile($compiled_name, $parser_options );


				$list = $less_files;
		$list[] = $compiled_name;
		$cache = implode("\n",$list);
		file_put_contents( $list_file, $cache );


				file_put_contents( $output_file, $compiled );


				self::CleanCache();

		return basename($output_file);
	}

	
	public static function Regen( $less_files, $parser_options = array(), $modify_vars = array() ){
		$parser_options['use_cache'] = false;
		return self::Get( $less_files, $parser_options, $modify_vars );
	}

	public static function Cache( &$less_files, $parser_options = array() ){


				$file = dirname(__FILE__) . '/Less.php';
		if( file_exists($file) && !class_exists('Less_Parser') ){
			require_once($file);
		}

		$parser_options['cache_dir'] = Less_Cache::$cache_dir;
		$parser = new Less_Parser($parser_options);


				foreach($less_files as $file_path => $uri_or_less ){

						if( strpos($uri_or_less,"\n") !== false ){
				$parser->Parse( $uri_or_less );
				continue;
			}

			$parser->ParseFile( $file_path, $uri_or_less );
		}

		$compiled = $parser->getCss();


		$less_files = $parser->allParsedFiles();

		return $compiled;
	}


	private static function OutputFile( $compiled_name, $parser_options ){

				if( !empty($parser_options['output']) ){

						if( preg_match('#[\\\\/]#',$parser_options['output']) ){
				return $parser_options['output'];
			}

			return Less_Cache::$cache_dir.$parser_options['output'];
		}

		return Less_Cache::$cache_dir.$compiled_name;
	}


	private static function CompiledName( $files, $extrahash ){

				$temp = array(Less_Version::cache_version);
		foreach($files as $file){
			$temp[] = filemtime($file)."\t".filesize($file)."\t".$file;
		}

		return Less_Cache::$prefix.sha1(json_encode($temp).$extrahash).'.css';
	}


	public static function SetCacheDir( $dir ){
		Less_Cache::$cache_dir = $dir;
	}

	public static function CheckCacheDir(){

		Less_Cache::$cache_dir = str_replace('\\','/',Less_Cache::$cache_dir);
		Less_Cache::$cache_dir = rtrim(Less_Cache::$cache_dir,'/').'/';

		if( !file_exists(Less_Cache::$cache_dir) ){
			if( !mkdir(Less_Cache::$cache_dir) ){
				throw new Less_Exception_Parser('Less.php cache directory couldn\'t be created: '.Less_Cache::$cache_dir);
			}

		}elseif( !is_dir(Less_Cache::$cache_dir) ){
			throw new Less_Exception_Parser('Less.php cache directory doesn\'t exist: '.Less_Cache::$cache_dir);

		}elseif( !is_writable(Less_Cache::$cache_dir) ){
			throw new Less_Exception_Parser('Less.php cache directory isn\'t writable: '.Less_Cache::$cache_dir);

		}

	}


	
	public static function CleanCache(){
		static $clean = false;

		if( $clean ){
			return;
		}

		$files = scandir(Less_Cache::$cache_dir);
		if( $files ){
			$check_time = time() - self::$gc_lifetime;
			foreach($files as $file){

								if( strpos($file,Less_Cache::$prefix) !== 0 ){
					continue;
				}

				$full_path = Less_Cache::$cache_dir . $file;

												if( !file_exists($full_path) ){
					continue;
				}
				$mtime = filemtime($full_path);

								if( $mtime > $check_time ){
					continue;
				}

				$parts = explode('.',$file);
				$type = array_pop($parts);


								if( $type === 'css' ){
					continue;
				}


								if( $type === 'list' ){
					self::ListFiles($full_path, $list, $css_file_name);
					if( $css_file_name ){
						$css_file = Less_Cache::$cache_dir . $css_file_name;
						if( file_exists($css_file) ){
							unlink($css_file);
						}
					}
				}

				unlink($full_path);
			}
		}

		$clean = true;
	}


	
	static function ListFiles($list_file, &$list, &$css_file_name ){

		$list = explode("\n",file_get_contents($list_file));

				$css_file_name = array_pop($list);

		if( !preg_match('/^' . Less_Cache::$prefix . '[a-f0-9]+\.css$/',$css_file_name) ){
			$list[] = $css_file_name;
			$css_file_name = false;
		}

	}

}
