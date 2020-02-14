<?php


class Less_Tree_Import extends Less_Tree{

	public $options;
	public $index;
	public $path;
	public $features;
	public $currentFileInfo;
	public $css;
	public $skip;
	public $root;
	public $type = 'Import';

    public function __construct($path, $features, $options, $index, $currentFileInfo = null ){
		$this->options = $options;
		$this->index = $index;
		$this->path = $path;
		$this->features = $features;
		$this->currentFileInfo = $currentFileInfo;

		if( is_array($options) ){
			$this->options += array('inline'=>false);

			if( isset($this->options['less']) || $this->options['inline'] ){
				$this->css = !isset($this->options['less']) || !$this->options['less'] || $this->options['inline'];
			} else {
				$pathValue = $this->getPath();
				if( $pathValue && preg_match('/css([\?;].*)?$/',$pathValue) ){
					$this->css = true;
				}
			}
		}
	}


    public function accept($visitor){

		if( $this->features ){
			$this->features = $visitor->visitObj($this->features);
		}
		$this->path = $visitor->visitObj($this->path);

		if( !$this->options['inline'] && $this->root ){
			$this->root = $visitor->visit($this->root);
		}
	}

    
    public function genCSS( $output ){
		if( $this->css ){

			$output->add( '@import ', $this->currentFileInfo, $this->index );

			$this->path->genCSS( $output );
			if( $this->features ){
				$output->add( ' ' );
				$this->features->genCSS( $output );
			}
			$output->add( ';' );
		}
	}

    public function toCSS(){
		$features = $this->features ? ' ' . $this->features->toCSS() : '';

		if ($this->css) {
			return "@import " . $this->path->toCSS() . $features . ";\n";
		} else {
			return "";
		}
	}

	
    public function getPath(){
		if ($this->path instanceof Less_Tree_Quoted) {
			$path = $this->path->value;
			$path = ( isset($this->css) || preg_match('/(\.[a-z]*$)|([\?;].*)$/',$path)) ? $path : $path . '.less';
		} else if ($this->path instanceof Less_Tree_URL) {
			$path = $this->path->value->value;
		}else{
			return null;
		}

				return preg_replace('/[\?#][^\?]*$/','',$path);
	}

    public function compileForImport( $env ){
		return new Less_Tree_Import( $this->path->compile($env), $this->features, $this->options, $this->index, $this->currentFileInfo);
	}

    public function compilePath($env) {
		$path = $this->path->compile($env);
		$rootpath = '';
		if( $this->currentFileInfo && $this->currentFileInfo['rootpath'] ){
			$rootpath = $this->currentFileInfo['rootpath'];
		}


		if( !($path instanceof Less_Tree_URL) ){
			if( $rootpath ){
				$pathValue = $path->value;
								if( $pathValue && Less_Environment::isPathRelative($pathValue) ){
					$path->value = $this->currentFileInfo['uri_root'].$pathValue;
				}
			}
			$path->value = Less_Environment::normalizePath($path->value);
		}



		return $path;
	}

    public function compile( $env ){

		$evald = $this->compileForImport($env);

				$path_and_uri = null;
		if( is_callable(Less_Parser::$options['import_callback']) ){
			$path_and_uri = call_user_func(Less_Parser::$options['import_callback'],$evald);
		}

		if( !$path_and_uri ){
			$path_and_uri = $evald->PathAndUri();
		}

		if( $path_and_uri ){
			list($full_path, $uri) = $path_and_uri;
		}else{
			$full_path = $uri = $evald->getPath();
		}


				if( $evald->skip( $full_path, $env) ){
			return array();
		}

		if( $this->options['inline'] ){
						
			Less_Parser::AddParsedFile($full_path);
			$contents = new Less_Tree_Anonymous( file_get_contents($full_path), 0, array(), true );

			if( $this->features ){
				return new Less_Tree_Media( array($contents), $this->features->value );
			}

			return array( $contents );
		}

				if( isset($this->options['optional']) && $this->options['optional'] && !file_exists($full_path) && (!$evald->css || !empty($this->currentFileInfo))) {
			return array();
		}


				if( $evald->css ){
			$features = ( $evald->features ? $evald->features->compile($env) : null );
			return new Less_Tree_Import( $this->compilePath( $env), $features, $this->options, $this->index);
		}


		return $this->ParseImport( $full_path, $uri, $env );
	}


	
    public function PathAndUri(){

		$evald_path = $this->getPath();

		if( $evald_path ){

			$import_dirs = array();

			if( Less_Environment::isPathRelative($evald_path) ){
								$import_dirs[ $this->currentFileInfo['currentDirectory'] ] = $this->currentFileInfo['uri_root'];

			}else{
								$import_dirs[ $this->currentFileInfo['entryPath'] ] = $this->currentFileInfo['entryUri'];

								$import_dirs[ $_SERVER['DOCUMENT_ROOT'] ] = '';

			}

						$import_dirs = array_merge( $import_dirs, Less_Parser::$options['import_dirs'] );


			foreach( $import_dirs as $rootpath => $rooturi){
				if( is_callable($rooturi) ){
					list($path, $uri) = call_user_func($rooturi, $evald_path);
					if( is_string($path) ){
						$full_path = $path;
						return array( $full_path, $uri );
					}
				}elseif( !empty($rootpath) ){


					if( $rooturi ){
						if( strpos($evald_path,$rooturi) === 0 ){
							$evald_path = substr( $evald_path, strlen($rooturi) );
						}
					}

					$path = rtrim($rootpath,'/\\').'/'.ltrim($evald_path,'/\\');

					if( file_exists($path) ){
						$full_path = Less_Environment::normalizePath($path);
						$uri = Less_Environment::normalizePath(dirname($rooturi.$evald_path));
						return array( $full_path, $uri );
					} elseif( file_exists($path.'.less') ){
						$full_path = Less_Environment::normalizePath($path.'.less');
						$uri = Less_Environment::normalizePath(dirname($rooturi.$evald_path.'.less'));
						return array( $full_path, $uri );
					}
				}
			}
		}
	}


	
    public function ParseImport( $full_path, $uri, $env ){

		$import_env = clone $env;
		if( (isset($this->options['reference']) && $this->options['reference']) || isset($this->currentFileInfo['reference']) ){
			$import_env->currentFileInfo['reference'] = true;
		}

		if( (isset($this->options['multiple']) && $this->options['multiple']) ){
			$import_env->importMultiple = true;
		}

		$parser = new Less_Parser($import_env);
		$root = $parser->parseFile($full_path, $uri, true);


		$ruleset = new Less_Tree_Ruleset(array(), $root->rules );
		$ruleset->evalImports($import_env);

		return $this->features ? new Less_Tree_Media($ruleset->rules, $this->features->value) : $ruleset->rules;
	}


	
	private function Skip($path, $env){

		$path = Less_Parser::winPath(realpath($path));

		if( $path && Less_Parser::FileParsed($path) ){

			if( isset($this->currentFileInfo['reference']) ){
				return true;
			}

			return !isset($this->options['multiple']) && !$env->importMultiple;
		}

	}
}
