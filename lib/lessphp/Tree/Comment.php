<?php


class Less_Tree_Comment extends Less_Tree{

	public $value;
	public $silent;
	public $isReferenced;
	public $currentFileInfo;
	public $type = 'Comment';

	public function __construct($value, $silent, $index = null, $currentFileInfo = null ){
		$this->value = $value;
		$this->silent = !! $silent;
		$this->currentFileInfo = $currentFileInfo;
	}

    
	public function genCSS( $output ){
									$output->add( trim($this->value) );	}

	public function toCSS(){
		return Less_Parser::$options['compress'] ? '' : $this->value;
	}

	public function isSilent(){
		$isReference = ($this->currentFileInfo && isset($this->currentFileInfo['reference']) && (!isset($this->isReferenced) || !$this->isReferenced) );
		$isCompressed = Less_Parser::$options['compress'] && !preg_match('/^\/\*!/', $this->value);
		return $this->silent || $isReference || $isCompressed;
	}

	public function compile(){
		return $this;
	}

	public function markReferenced(){
		$this->isReferenced = true;
	}

}
