<?php

require_once( dirname(__FILE__).'/Cache.php');


class Less_Parser{


	
	public static $default_options = array(
		'compress'				=> false,					'strictUnits'			=> false,					'strictMath'			=> false,					'relativeUrls'			=> true,					'urlArgs'				=> '',						'numPrecision'			=> 8,

		'import_dirs'			=> array(),
		'import_callback'		=> null,
		'cache_dir'				=> null,
		'cache_method'			=> 'php', 					'cache_callback_get'	=> null,
		'cache_callback_set'	=> null,

		'sourceMap'				=> false,					'sourceMapBasepath'		=> null,
		'sourceMapWriteTo'		=> null,
		'sourceMapURL'			=> null,

		'indentation' 			=> '  ',

		'plugins'				=> array(),

	);

	public static $options = array();


	private $input;						private $input_len;					private $pos;						private $saveStack = array();		private $furthest;
	private $mb_internal_encoding = ''; 
	
	private $env;

	protected $rules = array();

	private static $imports = array();

	public static $has_extends = false;

	public static $next_id = 0;

	
	public static $contentsMap = array();


	
	public function __construct( $env = null ){

						if( $env instanceof Less_Environment ){
			$this->env = $env;
		}else{
			$this->SetOptions(Less_Parser::$default_options);
			$this->Reset( $env );
		}

								if (ini_get('mbstring.func_overload')) {
			$this->mb_internal_encoding = ini_get('mbstring.internal_encoding');
			@ini_set('mbstring.internal_encoding', 'ascii');
		}

	}


	
	public function Reset( $options = null ){
		$this->rules = array();
		self::$imports = array();
		self::$has_extends = false;
		self::$imports = array();
		self::$contentsMap = array();

		$this->env = new Less_Environment($options);
		$this->env->Init();

				if( is_array($options) ){
			$this->SetOptions(Less_Parser::$default_options);
			$this->SetOptions($options);
		}
	}

	
	public function SetOptions( $options ){
		foreach($options as $option => $value){
			$this->SetOption($option,$value);
		}
	}

	
	public function SetOption($option,$value){

		switch($option){

			case 'import_dirs':
				$this->SetImportDirs($value);
			return;

			case 'cache_dir':
				if( is_string($value) ){
					Less_Cache::SetCacheDir($value);
					Less_Cache::CheckCacheDir();
				}
			return;
		}

		Less_Parser::$options[$option] = $value;
	}

	
	public function registerFunction($name, $callback) {
		$this->env->functions[$name] = $callback;
	}

	
	public function unregisterFunction($name) {
		if( isset($this->env->functions[$name]) )
			unset($this->env->functions[$name]);
	}


	
	public function getCss(){

		$precision = ini_get('precision');
		@ini_set('precision',16);
		$locale = setlocale(LC_NUMERIC, 0);
		setlocale(LC_NUMERIC, "C");

		try {

	 		$root = new Less_Tree_Ruleset(array(), $this->rules );
			$root->root = true;
			$root->firstRoot = true;


			$this->PreVisitors($root);

			self::$has_extends = false;
			$evaldRoot = $root->compile($this->env);



			$this->PostVisitors($evaldRoot);

			if( Less_Parser::$options['sourceMap'] ){
				$generator = new Less_SourceMap_Generator($evaldRoot, Less_Parser::$contentsMap, Less_Parser::$options );
												$css = $generator->generateCSS();
			}else{
				$css = $evaldRoot->toCSS();
			}

			if( Less_Parser::$options['compress'] ){
				$css = preg_replace('/(^(\s)+)|((\s)+$)/', '', $css);
			}

		} catch (Exception $exc) {
					}

				@ini_set('precision',$precision);
		setlocale(LC_NUMERIC, $locale);

						if ($this->mb_internal_encoding != '') {
			@ini_set("mbstring.internal_encoding", $this->mb_internal_encoding);
			$this->mb_internal_encoding = '';
		}

				if (!empty($exc)) {
			throw $exc;
		}



		return $css;
	}

	
	private function PreVisitors($root){

		if( Less_Parser::$options['plugins'] ){
			foreach(Less_Parser::$options['plugins'] as $plugin){
				if( !empty($plugin->isPreEvalVisitor) ){
					$plugin->run($root);
				}
			}
		}
	}


	
	private function PostVisitors($evaldRoot){

		$visitors = array();
		$visitors[] = new Less_Visitor_joinSelector();
		if( self::$has_extends ){
			$visitors[] = new Less_Visitor_processExtends();
		}
		$visitors[] = new Less_Visitor_toCSS();


		if( Less_Parser::$options['plugins'] ){
			foreach(Less_Parser::$options['plugins'] as $plugin){
				if( property_exists($plugin,'isPreEvalVisitor') && $plugin->isPreEvalVisitor ){
					continue;
				}

				if( property_exists($plugin,'isPreVisitor') && $plugin->isPreVisitor ){
					array_unshift( $visitors, $plugin);
				}else{
					$visitors[] = $plugin;
				}
			}
		}


		for($i = 0; $i < count($visitors); $i++ ){
			$visitors[$i]->run($evaldRoot);
		}

	}


	
	public function parse( $str, $file_uri = null ){

		if( !$file_uri ){
			$uri_root = '';
			$filename = 'anonymous-file-'.Less_Parser::$next_id++.'.less';
		}else{
			$file_uri = self::WinPath($file_uri);
			$filename = $file_uri;
			$uri_root = dirname($file_uri);
		}

		$previousFileInfo = $this->env->currentFileInfo;
		$uri_root = self::WinPath($uri_root);
		$this->SetFileInfo($filename, $uri_root);

		$this->input = $str;
		$this->_parse();

		if( $previousFileInfo ){
			$this->env->currentFileInfo = $previousFileInfo;
		}

		return $this;
	}


	
	public function parseFile( $filename, $uri_root = '', $returnRoot = false){

		if( !file_exists($filename) ){
			$this->Error(sprintf('File `%s` not found.', $filename));
		}


						if( !$returnRoot && !empty($uri_root) && basename($uri_root) == basename($filename) ){
			$uri_root = dirname($uri_root);
		}


		$previousFileInfo = $this->env->currentFileInfo;


		if( $filename ){
			$filename = self::WinPath(realpath($filename));
		}
		$uri_root = self::WinPath($uri_root);

		$this->SetFileInfo($filename, $uri_root);

		self::AddParsedFile($filename);

		if( $returnRoot ){
			$rules = $this->GetRules( $filename );
			$return = new Less_Tree_Ruleset(array(), $rules );
		}else{
			$this->_parse( $filename );
			$return = $this;
		}

		if( $previousFileInfo ){
			$this->env->currentFileInfo = $previousFileInfo;
		}

		return $return;
	}


	
	public function ModifyVars( $vars ){

		$this->input = Less_Parser::serializeVars( $vars );
		$this->_parse();

		return $this;
	}


	
	public function SetFileInfo( $filename, $uri_root = ''){

		$filename = Less_Environment::normalizePath($filename);
		$dirname = preg_replace('/[^\/\\\\]*$/','',$filename);

		if( !empty($uri_root) ){
			$uri_root = rtrim($uri_root,'/').'/';
		}

		$currentFileInfo = array();

				if( isset($this->env->currentFileInfo) ){
			$currentFileInfo['entryPath'] = $this->env->currentFileInfo['entryPath'];
			$currentFileInfo['entryUri'] = $this->env->currentFileInfo['entryUri'];
			$currentFileInfo['rootpath'] = $this->env->currentFileInfo['rootpath'];

		}else{
			$currentFileInfo['entryPath'] = $dirname;
			$currentFileInfo['entryUri'] = $uri_root;
			$currentFileInfo['rootpath'] = $dirname;
		}

		$currentFileInfo['currentDirectory'] = $dirname;
		$currentFileInfo['currentUri'] = $uri_root.basename($filename);
		$currentFileInfo['filename'] = $filename;
		$currentFileInfo['uri_root'] = $uri_root;


				if( isset($this->env->currentFileInfo['reference']) && $this->env->currentFileInfo['reference'] ){
			$currentFileInfo['reference'] = true;
		}

		$this->env->currentFileInfo = $currentFileInfo;
	}


	
	public function SetCacheDir( $dir ){

		if( !file_exists($dir) ){
			if( mkdir($dir) ){
				return true;
			}
			throw new Less_Exception_Parser('Less.php cache directory couldn\'t be created: '.$dir);

		}elseif( !is_dir($dir) ){
			throw new Less_Exception_Parser('Less.php cache directory doesn\'t exist: '.$dir);

		}elseif( !is_writable($dir) ){
			throw new Less_Exception_Parser('Less.php cache directory isn\'t writable: '.$dir);

		}else{
			$dir = self::WinPath($dir);
			Less_Cache::$cache_dir = rtrim($dir,'/').'/';
			return true;
		}
	}


	
	public function SetImportDirs( $dirs ){
		Less_Parser::$options['import_dirs'] = array();

		foreach($dirs as $path => $uri_root){

			$path = self::WinPath($path);
			if( !empty($path) ){
				$path = rtrim($path,'/').'/';
			}

			if ( !is_callable($uri_root) ){
				$uri_root = self::WinPath($uri_root);
				if( !empty($uri_root) ){
					$uri_root = rtrim($uri_root,'/').'/';
				}
			}

			Less_Parser::$options['import_dirs'][$path] = $uri_root;
		}
	}

	
	private function _parse( $file_path = null ){
		$this->rules = array_merge($this->rules, $this->GetRules( $file_path ));
	}


	
	private function GetRules( $file_path ){

		$this->SetInput($file_path);

		$cache_file = $this->CacheFile( $file_path );
		if( $cache_file ){
			if( Less_Parser::$options['cache_method'] == 'callback' ){
				if( is_callable(Less_Parser::$options['cache_callback_get']) ){
					$cache = call_user_func_array(
						Less_Parser::$options['cache_callback_get'],
						array($this, $file_path, $cache_file)
					);

					if( $cache ){
						$this->UnsetInput();
						return $cache;
					}
				}

			}elseif( file_exists($cache_file) ){
				switch(Less_Parser::$options['cache_method']){

															case 'serialize':
						$cache = unserialize(file_get_contents($cache_file));
						if( $cache ){
							touch($cache_file);
							$this->UnsetInput();
							return $cache;
						}
					break;


										case 'var_export':
					case 'php':
					$this->UnsetInput();
					return include($cache_file);
				}
			}
		}

		$rules = $this->parsePrimary();

		if( $this->pos < $this->input_len ){
			throw new Less_Exception_Chunk($this->input, null, $this->furthest, $this->env->currentFileInfo);
		}

		$this->UnsetInput();


				if( $cache_file ){
			if( Less_Parser::$options['cache_method'] == 'callback' ){
				if( is_callable(Less_Parser::$options['cache_callback_set']) ){
					call_user_func_array(
						Less_Parser::$options['cache_callback_set'],
						array($this, $file_path, $cache_file, $rules)
					);
				}

			}else{
								switch(Less_Parser::$options['cache_method']){
					case 'serialize':
						file_put_contents( $cache_file, serialize($rules) );
					break;
					case 'php':
						file_put_contents( $cache_file, '<?php return '.self::ArgString($rules).'; ?>' );
					break;
					case 'var_export':
												file_put_contents( $cache_file, '<?php return '.var_export($rules,true).'; ?>' );
					break;
				}

				Less_Cache::CleanCache();
			}
		}

		return $rules;
	}


	
	public function SetInput( $file_path ){

		if( $file_path ){
			$this->input = file_get_contents( $file_path );
		}

		$this->pos = $this->furthest = 0;

				$this->input = preg_replace('/\\G\xEF\xBB\xBF/', '', $this->input);
		$this->input_len = strlen($this->input);


		if( Less_Parser::$options['sourceMap'] && $this->env->currentFileInfo ){
			$uri = $this->env->currentFileInfo['currentUri'];
			Less_Parser::$contentsMap[$uri] = $this->input;
		}

	}


	
	public function UnsetInput(){
		unset($this->input, $this->pos, $this->input_len, $this->furthest);
		$this->saveStack = array();
	}


	public function CacheFile( $file_path ){

		if( $file_path && $this->CacheEnabled() ){

			$env = get_object_vars($this->env);
			unset($env['frames']);

			$parts = array();
			$parts[] = $file_path;
			$parts[] = filesize( $file_path );
			$parts[] = filemtime( $file_path );
			$parts[] = $env;
			$parts[] = Less_Version::cache_version;
			$parts[] = Less_Parser::$options['cache_method'];
			return Less_Cache::$cache_dir . Less_Cache::$prefix . base_convert( sha1(json_encode($parts) ), 16, 36) . '.lesscache';
		}
	}


	static function AddParsedFile($file){
		self::$imports[] = $file;
	}

	static function AllParsedFiles(){
		return self::$imports;
	}

	
	static function FileParsed($file){
		return in_array($file,self::$imports);
	}


	function save() {
		$this->saveStack[] = $this->pos;
	}

	private function restore() {
		$this->pos = array_pop($this->saveStack);
	}

	private function forget(){
		array_pop($this->saveStack);
	}


	private function isWhitespace($offset = 0) {
		return preg_match('/\s/',$this->input[ $this->pos + $offset]);
	}

	
	private function match($toks){

										
		foreach($toks as $tok){

			$char = $tok[0];

			if( $char === '/' ){
				$match = $this->MatchReg($tok);

				if( $match ){
					return count($match) === 1 ? $match[0] : $match;
				}

			}elseif( $char === '#' ){
				$match = $this->MatchChar($tok[1]);

			}else{
								$match = $this->$tok();

			}

			if( $match ){
				return $match;
			}
		}
	}

	
	private function MatchFuncs($toks){

		if( $this->pos < $this->input_len ){
			foreach($toks as $tok){
				$match = $this->$tok();
				if( $match ){
					return $match;
				}
			}
		}

	}

		private function MatchChar($tok){
		if( ($this->pos < $this->input_len) && ($this->input[$this->pos] === $tok) ){
			$this->skipWhitespace(1);
			return $tok;
		}
	}

		private function MatchReg($tok){

		if( preg_match($tok, $this->input, $match, 0, $this->pos) ){
			$this->skipWhitespace(strlen($match[0]));
			return $match;
		}
	}


	
	public function PeekReg($tok){
		return preg_match($tok, $this->input, $match, 0, $this->pos);
	}

	
	public function PeekChar($tok){
				return ($this->pos < $this->input_len) && ($this->input[$this->pos] === $tok );
	}


	
	public function skipWhitespace($length){

		$this->pos += $length;

		for(; $this->pos < $this->input_len; $this->pos++ ){
			$c = $this->input[$this->pos];

			if( ($c !== "\n") && ($c !== "\r") && ($c !== "\t") && ($c !== ' ') ){
				break;
			}
		}
	}


	
	public function expect($tok, $msg = NULL) {
		$result = $this->match( array($tok) );
		if (!$result) {
			$this->Error( $msg	? "Expected '" . $tok . "' got '" . $this->input[$this->pos] . "'" : $msg );
		} else {
			return $result;
		}
	}

	
	public function expectChar($tok, $msg = null ){
		$result = $this->MatchChar($tok);
		if( !$result ){
			$this->Error( $msg ? "Expected '" . $tok . "' got '" . $this->input[$this->pos] . "'" : $msg );
		}else{
			return $result;
		}
	}

																													
																private function parsePrimary(){
		$root = array();

		while( true ){

			if( $this->pos >= $this->input_len ){
				break;
			}

			$node = $this->parseExtend(true);
			if( $node ){
				$root = array_merge($root,$node);
				continue;
			}

						$node = $this->MatchFuncs( array( 'parseMixinDefinition', 'parseNameValue', 'parseRule', 'parseRuleset', 'parseMixinCall', 'parseComment', 'parseRulesetCall', 'parseDirective'));

			if( $node ){
				$root[] = $node;
			}elseif( !$this->MatchReg('/\\G[\s\n;]+/') ){
				break;
			}

            if( $this->PeekChar('}') ){
				break;
			}
		}

		return $root;
	}



				private function parseComment(){

		if( $this->input[$this->pos] !== '/' ){
			return;
		}

		if( $this->input[$this->pos+1] === '/' ){
			$match = $this->MatchReg('/\\G\/\/.*/');
			return $this->NewObj4('Less_Tree_Comment',array($match[0], true, $this->pos, $this->env->currentFileInfo));
		}

				$comment = $this->MatchReg('/\\G\/\*(?s).*?\*+\/\n?/');		if( $comment ){
			return $this->NewObj4('Less_Tree_Comment',array($comment[0], false, $this->pos, $this->env->currentFileInfo));
		}
	}

	private function parseComments(){
		$comments = array();

		while( $this->pos < $this->input_len ){
			$comment = $this->parseComment();
			if( !$comment ){
				break;
			}

			$comments[] = $comment;
		}

		return $comments;
	}



						private function parseEntitiesQuoted() {
		$j = $this->pos;
		$e = false;
		$index = $this->pos;

		if( $this->input[$this->pos] === '~' ){
			$j++;
			$e = true; 		}

		if( $this->input[$j] != '"' && $this->input[$j] !== "'" ){
			return;
		}

		if ($e) {
			$this->MatchChar('~');
		}

                                		$str = $this->MatchReg('/\\G"((?:[^"\\\\\r\n]|\\\\.|\\\\\r\n|\\\\[\n\r\f])*)"|\'((?:[^\'\\\\\r\n]|\\\\.|\\\\\r\n|\\\\[\n\r\f])*)\'/');

		if( $str ){
			$result = $str[0][0] == '"' ? $str[1] : $str[2];
			return $this->NewObj5('Less_Tree_Quoted',array($str[0], $result, $e, $index, $this->env->currentFileInfo) );
		}
		return;
	}


						private function parseEntitiesKeyword(){

				$k = $this->MatchReg('/\\G%|\\G[_A-Za-z-][_A-Za-z0-9-]*/');
		if( $k ){
			$k = $k[0];
			$color = $this->fromKeyword($k);
			if( $color ){
				return $color;
			}
			return $this->NewObj1('Less_Tree_Keyword',$k);
		}
	}

		private function FromKeyword( $keyword ){
		$keyword = strtolower($keyword);

		if( Less_Colors::hasOwnProperty($keyword) ){
						return $this->NewObj1('Less_Tree_Color',substr(Less_Colors::color($keyword), 1));
		}

		if( $keyword === 'transparent' ){
			return $this->NewObj3('Less_Tree_Color', array( array(0, 0, 0), 0, true));
		}
	}

											private function parseEntitiesCall(){
		$index = $this->pos;

		if( !preg_match('/\\G([\w-]+|%|progid:[\w\.]+)\(/', $this->input, $name,0,$this->pos) ){
			return;
		}
		$name = $name[1];
		$nameLC = strtolower($name);

		if ($nameLC === 'url') {
			return null;
		}

		$this->pos += strlen($name);

		if( $nameLC === 'alpha' ){
			$alpha_ret = $this->parseAlpha();
			if( $alpha_ret ){
				return $alpha_ret;
			}
		}

		$this->MatchChar('('); 
		$args = $this->parseEntitiesArguments();

		if( !$this->MatchChar(')') ){
			return;
		}

		if ($name) {
			return $this->NewObj4('Less_Tree_Call',array($name, $args, $index, $this->env->currentFileInfo) );
		}
	}

	
	private function parseEntitiesArguments(){

		$args = array();
		while( true ){
			$arg = $this->MatchFuncs( array('parseEntitiesAssignment','parseExpression') );
			if( !$arg ){
				break;
			}

			$args[] = $arg;
			if( !$this->MatchChar(',') ){
				break;
			}
		}
		return $args;
	}

	private function parseEntitiesLiteral(){
		return $this->MatchFuncs( array('parseEntitiesDimension','parseEntitiesColor','parseEntitiesQuoted','parseUnicodeDescriptor') );
	}

						private function parseEntitiesAssignment() {

		$key = $this->MatchReg('/\\G\w+(?=\s?=)/');
		if( !$key ){
			return;
		}

		if( !$this->MatchChar('=') ){
			return;
		}

		$value = $this->parseEntity();
		if( $value ){
			return $this->NewObj2('Less_Tree_Assignment',array($key[0], $value));
		}
	}

								private function parseEntitiesUrl(){


		if( $this->input[$this->pos] !== 'u' || !$this->matchReg('/\\Gurl\(/') ){
			return;
		}

		$value = $this->match( array('parseEntitiesQuoted','parseEntitiesVariable','/\\Gdata\:.*?[^\)]+/','/\\G(?:(?:\\\\[\(\)\'"])|[^\(\)\'"])+/') );
		if( !$value ){
			$value = '';
		}


		$this->expectChar(')');


		if( isset($value->value) || $value instanceof Less_Tree_Variable ){
			return $this->NewObj2('Less_Tree_Url',array($value, $this->env->currentFileInfo));
		}

		return $this->NewObj2('Less_Tree_Url', array( $this->NewObj1('Less_Tree_Anonymous',$value), $this->env->currentFileInfo) );
	}


									private function parseEntitiesVariable(){
		$index = $this->pos;
		if ($this->PeekChar('@') && ($name = $this->MatchReg('/\\G@@?[\w-]+/'))) {
			return $this->NewObj3('Less_Tree_Variable', array( $name[0], $index, $this->env->currentFileInfo));
		}
	}


		private function parseEntitiesVariableCurly() {
		$index = $this->pos;

		if( $this->input_len > ($this->pos+1) && $this->input[$this->pos] === '@' && ($curly = $this->MatchReg('/\\G@\{([\w-]+)\}/')) ){
			return $this->NewObj3('Less_Tree_Variable',array('@'.$curly[1], $index, $this->env->currentFileInfo));
		}
	}

								private function parseEntitiesColor(){
		if ($this->PeekChar('#') && ($rgb = $this->MatchReg('/\\G#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})/'))) {
			return $this->NewObj1('Less_Tree_Color',$rgb[1]);
		}
	}

						private function parseEntitiesDimension(){

		$c = @ord($this->input[$this->pos]);

				if (($c > 57 || $c < 43) || $c === 47 || $c == 44){
			return;
		}

		$value = $this->MatchReg('/\\G([+-]?\d*\.?\d+)(%|[a-z]+)?/');
		if( $value ){

			if( isset($value[2]) ){
				return $this->NewObj2('Less_Tree_Dimension', array($value[1],$value[2]));
			}
			return $this->NewObj1('Less_Tree_Dimension',$value[1]);
		}
	}


						function parseUnicodeDescriptor() {
		$ud = $this->MatchReg('/\\G(U\+[0-9a-fA-F?]+)(\-[0-9a-fA-F?]+)?/');
		if( $ud ){
			return $this->NewObj1('Less_Tree_UnicodeDescriptor', $ud[0]);
		}
	}


						private function parseEntitiesJavascript(){
		$e = false;
		$j = $this->pos;
		if( $this->input[$j] === '~' ){
			$j++;
			$e = true;
		}
		if( $this->input[$j] !== '`' ){
			return;
		}
		if( $e ){
			$this->MatchChar('~');
		}
		$str = $this->MatchReg('/\\G`([^`]*)`/');
		if( $str ){
			return $this->NewObj3('Less_Tree_Javascript', array($str[1], $this->pos, $e));
		}
	}


						private function parseVariable(){
		if ($this->PeekChar('@') && ($name = $this->MatchReg('/\\G(@[\w-]+)\s*:/'))) {
			return $name[1];
		}
	}


						private function parseRulesetCall(){

		if( $this->input[$this->pos] === '@' && ($name = $this->MatchReg('/\\G(@[\w-]+)\s*\(\s*\)\s*;/')) ){
			return $this->NewObj1('Less_Tree_RulesetCall', $name[1] );
		}
	}


				function parseExtend($isRule = false){

		$index = $this->pos;
		$extendList = array();


		if( !$this->MatchReg( $isRule ? '/\\G&:extend\(/' : '/\\G:extend\(/' ) ){ return; }

		do{
			$option = null;
			$elements = array();
			while( true ){
				$option = $this->MatchReg('/\\G(all)(?=\s*(\)|,))/');
				if( $option ){ break; }
				$e = $this->parseElement();
				if( !$e ){ break; }
				$elements[] = $e;
			}

			if( $option ){
				$option = $option[1];
			}

			$extendList[] = $this->NewObj3('Less_Tree_Extend', array( $this->NewObj1('Less_Tree_Selector',$elements), $option, $index ));

		}while( $this->MatchChar(",") );

		$this->expect('/\\G\)/');

		if( $isRule ){
			$this->expect('/\\G;/');
		}

		return $extendList;
	}


												private function parseMixinCall(){

		$char = $this->input[$this->pos];
		if( $char !== '.' && $char !== '#' ){
			return;
		}

		$index = $this->pos;
		$this->save(); 
		$elements = $this->parseMixinCallElements();

		if( $elements ){

			if( $this->MatchChar('(') ){
				$returned = $this->parseMixinArgs(true);
				$args = $returned['args'];
				$this->expectChar(')');
			}else{
				$args = array();
			}

			$important = $this->parseImportant();

			if( $this->parseEnd() ){
				$this->forget();
				return $this->NewObj5('Less_Tree_Mixin_Call', array( $elements, $args, $index, $this->env->currentFileInfo, $important));
			}
		}

		$this->restore();
	}


	private function parseMixinCallElements(){
		$elements = array();
		$c = null;

		while( true ){
			$elemIndex = $this->pos;
			$e = $this->MatchReg('/\\G[#.](?:[\w-]|\\\\(?:[A-Fa-f0-9]{1,6} ?|[^A-Fa-f0-9]))+/');
			if( !$e ){
				break;
			}
			$elements[] = $this->NewObj4('Less_Tree_Element', array($c, $e[0], $elemIndex, $this->env->currentFileInfo));
			$c = $this->MatchChar('>');
		}

		return $elements;
	}



	
	private function parseMixinArgs( $isCall ){
		$expressions = array();
		$argsSemiColon = array();
		$isSemiColonSeperated = null;
		$argsComma = array();
		$expressionContainsNamed = null;
		$name = null;
		$returner = array('args'=>array(), 'variadic'=> false);

		$this->save();

		while( true ){
			if( $isCall ){
				$arg = $this->MatchFuncs( array( 'parseDetachedRuleset','parseExpression' ) );
			} else {
				$this->parseComments();
				if( $this->input[ $this->pos ] === '.' && $this->MatchReg('/\\G\.{3}/') ){
					$returner['variadic'] = true;
					if( $this->MatchChar(";") && !$isSemiColonSeperated ){
						$isSemiColonSeperated = true;
					}

					if( $isSemiColonSeperated ){
						$argsSemiColon[] = array('variadic'=>true);
					}else{
						$argsComma[] = array('variadic'=>true);
					}
					break;
				}
				$arg = $this->MatchFuncs( array('parseEntitiesVariable','parseEntitiesLiteral','parseEntitiesKeyword') );
			}

			if( !$arg ){
				break;
			}


			$nameLoop = null;
			if( $arg instanceof Less_Tree_Expression ){
				$arg->throwAwayComments();
			}
			$value = $arg;
			$val = null;

			if( $isCall ){
								if( property_exists($arg,'value') && count($arg->value) == 1 ){
					$val = $arg->value[0];
				}
			} else {
				$val = $arg;
			}


			if( $val instanceof Less_Tree_Variable ){

				if( $this->MatchChar(':') ){
					if( $expressions ){
						if( $isSemiColonSeperated ){
							$this->Error('Cannot mix ; and , as delimiter types');
						}
						$expressionContainsNamed = true;
					}

																				$value = null;
					if( $isCall ){
						$value = $this->parseDetachedRuleset();
					}
					if( !$value ){
						$value = $this->parseExpression();
					}

					if( !$value ){
						if( $isCall ){
							$this->Error('could not understand value for named argument');
						} else {
							$this->restore();
							$returner['args'] = array();
							return $returner;
						}
					}

					$nameLoop = ($name = $val->name);
				}elseif( !$isCall && $this->MatchReg('/\\G\.{3}/') ){
					$returner['variadic'] = true;
					if( $this->MatchChar(";") && !$isSemiColonSeperated ){
						$isSemiColonSeperated = true;
					}
					if( $isSemiColonSeperated ){
						$argsSemiColon[] = array('name'=> $arg->name, 'variadic' => true);
					}else{
						$argsComma[] = array('name'=> $arg->name, 'variadic' => true);
					}
					break;
				}elseif( !$isCall ){
					$name = $nameLoop = $val->name;
					$value = null;
				}
			}

			if( $value ){
				$expressions[] = $value;
			}

			$argsComma[] = array('name'=>$nameLoop, 'value'=>$value );

			if( $this->MatchChar(',') ){
				continue;
			}

			if( $this->MatchChar(';') || $isSemiColonSeperated ){

				if( $expressionContainsNamed ){
					$this->Error('Cannot mix ; and , as delimiter types');
				}

				$isSemiColonSeperated = true;

				if( count($expressions) > 1 ){
					$value = $this->NewObj1('Less_Tree_Value', $expressions);
				}
				$argsSemiColon[] = array('name'=>$name, 'value'=>$value );

				$name = null;
				$expressions = array();
				$expressionContainsNamed = false;
			}
		}

		$this->forget();
		$returner['args'] = ($isSemiColonSeperated ? $argsSemiColon : $argsComma);
		return $returner;
	}



																				private function parseMixinDefinition(){
		$cond = null;

		$char = $this->input[$this->pos];
		if( ($char !== '.' && $char !== '#') || ($char === '{' && $this->PeekReg('/\\G[^{]*\}/')) ){
			return;
		}

		$this->save();

		$match = $this->MatchReg('/\\G([#.](?:[\w-]|\\\(?:[A-Fa-f0-9]{1,6} ?|[^A-Fa-f0-9]))+)\s*\(/');
		if( $match ){
			$name = $match[1];

			$argInfo = $this->parseMixinArgs( false );
			$params = $argInfo['args'];
			$variadic = $argInfo['variadic'];


																		if( !$this->MatchChar(')') ){
				$this->furthest = $this->pos;
				$this->restore();
				return;
			}


			$this->parseComments();

			if ($this->MatchReg('/\\Gwhen/')) { 				$cond = $this->expect('parseConditions', 'Expected conditions');
			}

			$ruleset = $this->parseBlock();

			if( is_array($ruleset) ){
				$this->forget();
				return $this->NewObj5('Less_Tree_Mixin_Definition', array( $name, $params, $ruleset, $cond, $variadic));
			}

			$this->restore();
		}else{
			$this->forget();
		}
	}

					private function parseEntity(){

		return $this->MatchFuncs( array('parseEntitiesLiteral','parseEntitiesVariable','parseEntitiesUrl','parseEntitiesCall','parseEntitiesKeyword','parseEntitiesJavascript','parseComment') );
	}

						private function parseEnd(){
		return $this->MatchChar(';') || $this->PeekChar('}');
	}

						private function parseAlpha(){

		if ( ! $this->MatchReg('/\\G\(opacity=/i')) {
			return;
		}

		$value = $this->MatchReg('/\\G[0-9]+/');
		if( $value ){
			$value = $value[0];
		}else{
			$value = $this->parseEntitiesVariable();
			if( !$value ){
				return;
			}
		}

		$this->expectChar(')');
		return $this->NewObj1('Less_Tree_Alpha',$value);
	}


													private function parseElement(){
		$c = $this->parseCombinator();
		$index = $this->pos;

		$e = $this->match( array('/\\G(?:\d+\.\d+|\d+)%/', '/\\G(?:[.#]?|:*)(?:[\w-]|[^\x00-\x9f]|\\\\(?:[A-Fa-f0-9]{1,6} ?|[^A-Fa-f0-9]))+/',
			'#*', '#&', 'parseAttribute', '/\\G\([^()@]+\)/', '/\\G[\.#](?=@)/', 'parseEntitiesVariableCurly') );

		if( is_null($e) ){
			$this->save();
			if( $this->MatchChar('(') ){
				if( ($v = $this->parseSelector()) && $this->MatchChar(')') ){
					$e = $this->NewObj1('Less_Tree_Paren',$v);
					$this->forget();
				}else{
					$this->restore();
				}
			}else{
				$this->forget();
			}
		}

		if( !is_null($e) ){
			return $this->NewObj4('Less_Tree_Element',array( $c, $e, $index, $this->env->currentFileInfo));
		}
	}

									private function parseCombinator(){
		if( $this->pos < $this->input_len ){
			$c = $this->input[$this->pos];
			if ($c === '>' || $c === '+' || $c === '~' || $c === '|' || $c === '^' ){

				$this->pos++;
				if( $this->input[$this->pos] === '^' ){
					$c = '^^';
					$this->pos++;
				}

				$this->skipWhitespace(0);

				return $c;
			}

			if( $this->pos > 0 && $this->isWhitespace(-1) ){
				return ' ';
			}
		}
	}

					private function parseLessSelector(){
		return $this->parseSelector(true);
	}

									private function parseSelector( $isLess = false ){
		$elements = array();
		$extendList = array();
		$condition = null;
		$when = false;
		$extend = false;
		$e = null;
		$c = null;
		$index = $this->pos;

		while( ($isLess && ($extend = $this->parseExtend())) || ($isLess && ($when = $this->MatchReg('/\\Gwhen/') )) || ($e = $this->parseElement()) ){
			if( $when ){
				$condition = $this->expect('parseConditions', 'expected condition');
			}elseif( $condition ){
							}elseif( $extend ){
				$extendList = array_merge($extendList,$extend);
			}else{
																	if( $this->pos < $this->input_len ){
					$c = $this->input[ $this->pos ];
				}
				$elements[] = $e;
				$e = null;
			}

			if( $c === '{' || $c === '}' || $c === ';' || $c === ',' || $c === ')') { break; }
		}

		if( $elements ){
			return $this->NewObj5('Less_Tree_Selector',array($elements, $extendList, $condition, $index, $this->env->currentFileInfo));
		}
		if( $extendList ) {
			$this->Error('Extend must be used to extend a selector, it cannot be used on its own');
		}
	}

	private function parseTag(){
		return ( $tag = $this->MatchReg('/\\G[A-Za-z][A-Za-z-]*[0-9]?/') ) ? $tag : $this->MatchChar('*');
	}

	private function parseAttribute(){

		$val = null;

		if( !$this->MatchChar('[') ){
			return;
		}

		$key = $this->parseEntitiesVariableCurly();
		if( !$key ){
			$key = $this->expect('/\\G(?:[_A-Za-z0-9-\*]*\|)?(?:[_A-Za-z0-9-]|\\\\.)+/');
		}

		$op = $this->MatchReg('/\\G[|~*$^]?=/');
		if( $op ){
			$val = $this->match( array('parseEntitiesQuoted','/\\G[0-9]+%/','/\\G[\w-]+/','parseEntitiesVariableCurly') );
		}

		$this->expectChar(']');

		return $this->NewObj3('Less_Tree_Attribute',array( $key, $op[0], $val));
	}

					private function parseBlock(){
		if( $this->MatchChar('{') ){
			$content = $this->parsePrimary();
			if( $this->MatchChar('}') ){
				return $content;
			}
		}
	}

	private function parseBlockRuleset(){
		$block = $this->parseBlock();

		if( $block ){
			$block = $this->NewObj2('Less_Tree_Ruleset',array( null, $block));
		}

		return $block;
	}

	private function parseDetachedRuleset(){
		$blockRuleset = $this->parseBlockRuleset();
		if( $blockRuleset ){
			return $this->NewObj1('Less_Tree_DetachedRuleset',$blockRuleset);
		}
	}

				private function parseRuleset(){
		$selectors = array();

		$this->save();

		while( true ){
			$s = $this->parseLessSelector();
			if( !$s ){
				break;
			}
			$selectors[] = $s;
			$this->parseComments();

			if( $s->condition && count($selectors) > 1 ){
				$this->Error('Guards are only currently allowed on a single selector.');
			}

			if( !$this->MatchChar(',') ){
				break;
			}
			if( $s->condition ){
				$this->Error('Guards are only currently allowed on a single selector.');
			}
			$this->parseComments();
		}


		if( $selectors ){
			$rules = $this->parseBlock();
			if( is_array($rules) ){
				$this->forget();
				return $this->NewObj2('Less_Tree_Ruleset',array( $selectors, $rules)); 			}
		}

				$this->furthest = $this->pos;
		$this->restore();
	}

	
	private function parseNameValue(){

		$index = $this->pos;
		$this->save();


				$match = $this->MatchReg('/\\G([a-zA-Z\-]+)\s*:\s*([\'"]?[#a-zA-Z0-9\-%\.,]+?[\'"]?) *(! *important)?\s*([;}])/');
		if( $match ){

			if( $match[4] == '}' ){
				$this->pos = $index + strlen($match[0])-1;
			}

			if( $match[3] ){
				$match[2] .= ' !important';
			}

			return $this->NewObj4('Less_Tree_NameValue',array( $match[1], $match[2], $index, $this->env->currentFileInfo));
		}

		$this->restore();
	}


	private function parseRule( $tryAnonymous = null ){

		$merge = false;
		$startOfRule = $this->pos;

		$c = $this->input[$this->pos];
		if( $c === '.' || $c === '#' || $c === '&' ){
			return;
		}

		$this->save();
		$name = $this->MatchFuncs( array('parseVariable','parseRuleProperty'));

		if( $name ){

			$isVariable = is_string($name);

			$value = null;
			if( $isVariable ){
				$value = $this->parseDetachedRuleset();
			}

			$important = null;
			if( !$value ){

																if( !$tryAnonymous && (Less_Parser::$options['compress'] || $isVariable) ){
					$value = $this->MatchFuncs( array('parseValue','parseAnonymousValue'));
				}else{
					$value = $this->MatchFuncs( array('parseAnonymousValue','parseValue'));
				}

				$important = $this->parseImportant();

																if( !$isVariable && is_array($name) ){
					$nm = array_pop($name);
					if( $nm->value ){
						$merge = $nm->value;
					}
				}
			}


			if( $value && $this->parseEnd() ){
				$this->forget();
				return $this->NewObj6('Less_Tree_Rule',array( $name, $value, $important, $merge, $startOfRule, $this->env->currentFileInfo));
			}else{
				$this->furthest = $this->pos;
				$this->restore();
				if( $value && !$tryAnonymous ){
					return $this->parseRule(true);
				}
			}
		}else{
			$this->forget();
		}
	}

	function parseAnonymousValue(){

		if( preg_match('/\\G([^@+\/\'"*`(;{}-]*);/',$this->input, $match, 0, $this->pos) ){
			$this->pos += strlen($match[1]);
			return $this->NewObj1('Less_Tree_Anonymous',$match[1]);
		}
	}

											private function parseImport(){

		$this->save();

		$dir = $this->MatchReg('/\\G@import?\s+/');

		if( $dir ){
			$options = $this->parseImportOptions();
			$path = $this->MatchFuncs( array('parseEntitiesQuoted','parseEntitiesUrl'));

			if( $path ){
				$features = $this->parseMediaFeatures();
				if( $this->MatchChar(';') ){
					if( $features ){
						$features = $this->NewObj1('Less_Tree_Value',$features);
					}

					$this->forget();
					return $this->NewObj5('Less_Tree_Import',array( $path, $features, $options, $this->pos, $this->env->currentFileInfo));
				}
			}
		}

		$this->restore();
	}

	private function parseImportOptions(){

		$options = array();

				if( !$this->MatchChar('(') ){
			return $options;
		}
		do{
			$optionName = $this->parseImportOption();
			if( $optionName ){
				$value = true;
				switch( $optionName ){
					case "css":
						$optionName = "less";
						$value = false;
					break;
					case "once":
						$optionName = "multiple";
						$value = false;
					break;
				}
				$options[$optionName] = $value;
				if( !$this->MatchChar(',') ){ break; }
			}
		}while( $optionName );
		$this->expectChar(')');
		return $options;
	}

	private function parseImportOption(){
		$opt = $this->MatchReg('/\\G(less|css|multiple|once|inline|reference|optional)/');
		if( $opt ){
			return $opt[1];
		}
	}

	private function parseMediaFeature() {
		$nodes = array();

		do{
			$e = $this->MatchFuncs(array('parseEntitiesKeyword','parseEntitiesVariable'));
			if( $e ){
				$nodes[] = $e;
			} elseif ($this->MatchChar('(')) {
				$p = $this->parseProperty();
				$e = $this->parseValue();
				if ($this->MatchChar(')')) {
					if ($p && $e) {
						$r = $this->NewObj7('Less_Tree_Rule', array( $p, $e, null, null, $this->pos, $this->env->currentFileInfo, true));
						$nodes[] = $this->NewObj1('Less_Tree_Paren',$r);
					} elseif ($e) {
						$nodes[] = $this->NewObj1('Less_Tree_Paren',$e);
					} else {
						return null;
					}
				} else
					return null;
			}
		} while ($e);

		if ($nodes) {
			return $this->NewObj1('Less_Tree_Expression',$nodes);
		}
	}

	private function parseMediaFeatures() {
		$features = array();

		do{
			$e = $this->parseMediaFeature();
			if( $e ){
				$features[] = $e;
				if (!$this->MatchChar(',')) break;
			}else{
				$e = $this->parseEntitiesVariable();
				if( $e ){
					$features[] = $e;
					if (!$this->MatchChar(',')) break;
				}
			}
		} while ($e);

		return $features ? $features : null;
	}

	private function parseMedia() {
		if( $this->MatchReg('/\\G@media/') ){
			$features = $this->parseMediaFeatures();
			$rules = $this->parseBlock();

			if( is_array($rules) ){
				return $this->NewObj4('Less_Tree_Media',array( $rules, $features, $this->pos, $this->env->currentFileInfo));
			}
		}
	}


						private function parseDirective(){

		if( !$this->PeekChar('@') ){
			return;
		}

		$rules = null;
		$index = $this->pos;
		$hasBlock = true;
		$hasIdentifier = false;
		$hasExpression = false;
		$hasUnknown = false;


		$value = $this->MatchFuncs(array('parseImport','parseMedia'));
		if( $value ){
			return $value;
		}

		$this->save();

		$name = $this->MatchReg('/\\G@[a-z-]+/');

		if( !$name ) return;
		$name = $name[0];


		$nonVendorSpecificName = $name;
		$pos = strpos($name,'-', 2);
		if( $name[1] == '-' && $pos > 0 ){
			$nonVendorSpecificName = "@" . substr($name, $pos + 1);
		}


		switch( $nonVendorSpecificName ){
			
			case "@charset":
				$hasIdentifier = true;
				$hasBlock = false;
				break;
			case "@namespace":
				$hasExpression = true;
				$hasBlock = false;
				break;
			case "@keyframes":
				$hasIdentifier = true;
				break;
			case "@host":
			case "@page":
			case "@document":
			case "@supports":
				$hasUnknown = true;
				break;
		}

		if( $hasIdentifier ){
			$value = $this->parseEntity();
			if( !$value ){
				$this->error("expected " . $name . " identifier");
			}
		} else if( $hasExpression ){
			$value = $this->parseExpression();
			if( !$value ){
				$this->error("expected " . $name. " expression");
			}
		} else if ($hasUnknown) {

			$value = $this->MatchReg('/\\G[^{;]+/');
			if( $value ){
				$value = $this->NewObj1('Less_Tree_Anonymous',trim($value[0]));
			}
		}

		if( $hasBlock ){
			$rules = $this->parseBlockRuleset();
		}

		if( $rules || (!$hasBlock && $value && $this->MatchChar(';'))) {
			$this->forget();
			return $this->NewObj5('Less_Tree_Directive',array($name, $value, $rules, $index, $this->env->currentFileInfo));
		}

		$this->restore();
	}


									private function parseValue(){
		$expressions = array();

		do{
			$e = $this->parseExpression();
			if( $e ){
				$expressions[] = $e;
				if (! $this->MatchChar(',')) {
					break;
				}
			}
		}while($e);

		if( $expressions ){
			return $this->NewObj1('Less_Tree_Value',$expressions);
		}
	}

	private function parseImportant (){
		if( $this->PeekChar('!') && $this->MatchReg('/\\G! *important/') ){
			return ' !important';
		}
	}

	private function parseSub (){

		if( $this->MatchChar('(') ){
			$a = $this->parseAddition();
			if( $a ){
				$this->expectChar(')');
				return $this->NewObj2('Less_Tree_Expression',array( array($a), true) ); 			}
		}
	}


	
	function parseMultiplication(){

		$return = $m = $this->parseOperand();
		if( $return ){
			while( true ){

				$isSpaced = $this->isWhitespace( -1 );

				if( $this->PeekReg('/\\G\/[*\/]/') ){
					break;
				}

				$op = $this->MatchChar('/');
				if( !$op ){
					$op = $this->MatchChar('*');
					if( !$op ){
						break;
					}
				}

				$a = $this->parseOperand();

				if(!$a) { break; }

				$m->parensInOp = true;
				$a->parensInOp = true;
				$return = $this->NewObj3('Less_Tree_Operation',array( $op, array( $return, $a ), $isSpaced) );
			}
		}
		return $return;

	}


	
	private function parseAddition (){

		$return = $m = $this->parseMultiplication();
		if( $return ){
			while( true ){

				$isSpaced = $this->isWhitespace( -1 );

				$op = $this->MatchReg('/\\G[-+]\s+/');
				if( $op ){
					$op = $op[0];
				}else{
					if( !$isSpaced ){
						$op = $this->match(array('#+','#-'));
					}
					if( !$op ){
						break;
					}
				}

				$a = $this->parseMultiplication();
				if( !$a ){
					break;
				}

				$m->parensInOp = true;
				$a->parensInOp = true;
				$return = $this->NewObj3('Less_Tree_Operation',array($op, array($return, $a), $isSpaced));
			}
		}

		return $return;
	}


	
	private function parseConditions() {
		$index = $this->pos;
		$return = $a = $this->parseCondition();
		if( $a ){
			while( true ){
				if( !$this->PeekReg('/\\G,\s*(not\s*)?\(/') ||  !$this->MatchChar(',') ){
					break;
				}
				$b = $this->parseCondition();
				if( !$b ){
					break;
				}

				$return = $this->NewObj4('Less_Tree_Condition',array('or', $return, $b, $index));
			}
			return $return;
		}
	}

	private function parseCondition() {
		$index = $this->pos;
		$negate = false;
		$c = null;

		if ($this->MatchReg('/\\Gnot/')) $negate = true;
		$this->expectChar('(');
		$a = $this->MatchFuncs(array('parseAddition','parseEntitiesKeyword','parseEntitiesQuoted'));

		if( $a ){
			$op = $this->MatchReg('/\\G(?:>=|<=|=<|[<=>])/');
			if( $op ){
				$b = $this->MatchFuncs(array('parseAddition','parseEntitiesKeyword','parseEntitiesQuoted'));
				if( $b ){
					$c = $this->NewObj5('Less_Tree_Condition',array($op[0], $a, $b, $index, $negate));
				} else {
					$this->Error('Unexpected expression');
				}
			} else {
				$k = $this->NewObj1('Less_Tree_Keyword','true');
				$c = $this->NewObj5('Less_Tree_Condition',array('=', $a, $k, $index, $negate));
			}
			$this->expectChar(')');
			return $this->MatchReg('/\\Gand/') ? $this->NewObj3('Less_Tree_Condition',array('and', $c, $this->parseCondition())) : $c;
		}
	}

	
	private function parseOperand (){

		$negate = false;
		$offset = $this->pos+1;
		if( $offset >= $this->input_len ){
			return;
		}
		$char = $this->input[$offset];
		if( $char === '@' || $char === '(' ){
			$negate = $this->MatchChar('-');
		}

		$o = $this->MatchFuncs(array('parseSub','parseEntitiesDimension','parseEntitiesColor','parseEntitiesVariable','parseEntitiesCall'));

		if( $negate ){
			$o->parensInOp = true;
			$o = $this->NewObj1('Less_Tree_Negative',$o);
		}

		return $o;
	}


	
	private function parseExpression (){
		$entities = array();

		do{
			$e = $this->MatchFuncs(array('parseAddition','parseEntity'));
			if( $e ){
				$entities[] = $e;
								if( !$this->PeekReg('/\\G\/[\/*]/') ){
					$delim = $this->MatchChar('/');
					if( $delim ){
						$entities[] = $this->NewObj1('Less_Tree_Anonymous',$delim);
					}
				}
			}
		}while($e);

		if( $entities ){
			return $this->NewObj1('Less_Tree_Expression',$entities);
		}
	}


	
	private function parseProperty (){
		$name = $this->MatchReg('/\\G(\*?-?[_a-zA-Z0-9-]+)\s*:/');
		if( $name ){
			return $name[1];
		}
	}


	
	private function parseRuleProperty(){
		$offset = $this->pos;
		$name = array();
		$index = array();
		$length = 0;


		$this->rulePropertyMatch('/\\G(\*?)/', $offset, $length, $index, $name );
		while( $this->rulePropertyMatch('/\\G((?:[\w-]+)|(?:@\{[\w-]+\}))/', $offset, $length, $index, $name )); 
		if( (count($name) > 1) && $this->rulePropertyMatch('/\\G\s*((?:\+_|\+)?)\s*:/', $offset, $length, $index, $name) ){
									$this->skipWhitespace($length);

			if( $name[0] === '' ){
				array_shift($name);
				array_shift($index);
			}
			foreach($name as $k => $s ){
				if( !$s || $s[0] !== '@' ){
					$name[$k] = $this->NewObj1('Less_Tree_Keyword',$s);
				}else{
					$name[$k] = $this->NewObj3('Less_Tree_Variable',array('@' . substr($s,2,-1), $index[$k], $this->env->currentFileInfo));
				}
			}
			return $name;
		}


	}

	private function rulePropertyMatch( $re, &$offset, &$length,  &$index, &$name ){
		preg_match($re, $this->input, $a, 0, $offset);
		if( $a ){
			$index[] = $this->pos + $length;
			$length += strlen($a[0]);
			$offset += strlen($a[0]);
			$name[] = $a[1];
			return true;
		}
	}

	public static function serializeVars( $vars ){
		$s = '';

		foreach($vars as $name => $value){
			$s .= (($name[0] === '@') ? '' : '@') . $name .': '. $value . ((substr($value,-1) === ';') ? '' : ';');
		}

		return $s;
	}


	
	public static function is_method($a,$b){
		return is_object($a) && method_exists($a,$b);
	}


	
	public static function round($i, $precision = 0){

		$precision = pow(10,$precision);
		$i = $i*$precision;

		$ceil = ceil($i);
		$floor = floor($i);
		if( ($ceil - $i) <= ($i - $floor) ){
			return $ceil/$precision;
		}else{
			return $floor/$precision;
		}
	}


	
	public function NewObj0($class){
		$obj = new $class();
		if( $this->CacheEnabled() ){
			$obj->cache_string = ' new '.$class.'()';
		}
		return $obj;
	}

	public function NewObj1($class, $arg){
		$obj = new $class( $arg );
		if( $this->CacheEnabled() ){
			$obj->cache_string = ' new '.$class.'('.Less_Parser::ArgString($arg).')';
		}
		return $obj;
	}

	public function NewObj2($class, $args){
		$obj = new $class( $args[0], $args[1] );
		if( $this->CacheEnabled() ){
			$this->ObjCache( $obj, $class, $args);
		}
		return $obj;
	}

	public function NewObj3($class, $args){
		$obj = new $class( $args[0], $args[1], $args[2] );
		if( $this->CacheEnabled() ){
			$this->ObjCache( $obj, $class, $args);
		}
		return $obj;
	}

	public function NewObj4($class, $args){
		$obj = new $class( $args[0], $args[1], $args[2], $args[3] );
		if( $this->CacheEnabled() ){
			$this->ObjCache( $obj, $class, $args);
		}
		return $obj;
	}

	public function NewObj5($class, $args){
		$obj = new $class( $args[0], $args[1], $args[2], $args[3], $args[4] );
		if( $this->CacheEnabled() ){
			$this->ObjCache( $obj, $class, $args);
		}
		return $obj;
	}

	public function NewObj6($class, $args){
		$obj = new $class( $args[0], $args[1], $args[2], $args[3], $args[4], $args[5] );
		if( $this->CacheEnabled() ){
			$this->ObjCache( $obj, $class, $args);
		}
		return $obj;
	}

	public function NewObj7($class, $args){
		$obj = new $class( $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6] );
		if( $this->CacheEnabled() ){
			$this->ObjCache( $obj, $class, $args);
		}
		return $obj;
	}

		public function ObjCache($obj, $class, $args=array()){
		$obj->cache_string = ' new '.$class.'('. self::ArgCache($args).')';
	}

	public function ArgCache($args){
		return implode(',',array_map( array('Less_Parser','ArgString'),$args));
	}


	
	public static function ArgString($arg){

		$type = gettype($arg);

		if( $type === 'object'){
			$string = $arg->cache_string;
			unset($arg->cache_string);
			return $string;

		}elseif( $type === 'array' ){
			$string = ' Array(';
			foreach($arg as $k => $a){
				$string .= var_export($k,true).' => '.self::ArgString($a).',';
			}
			return $string . ')';
		}

		return var_export($arg,true);
	}

	public function Error($msg){
		throw new Less_Exception_Parser($msg, null, $this->furthest, $this->env->currentFileInfo);
	}

	public static function WinPath($path){
		return str_replace('\\', '/', $path);
	}

	public function CacheEnabled(){
		return (Less_Parser::$options['cache_method'] && (Less_Cache::$cache_dir || (Less_Parser::$options['cache_method'] == 'callback')));
	}

}
