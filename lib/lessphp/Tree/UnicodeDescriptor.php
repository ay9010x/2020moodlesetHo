<?php


class Less_Tree_UnicodeDescriptor extends Less_Tree{

	public $value;
	public $type = 'UnicodeDescriptor';

	public function __construct($value){
		$this->value = $value;
	}

    
	public function genCSS( $output ){
		$output->add( $this->value );
	}

	public function compile(){
		return $this;
	}
}

