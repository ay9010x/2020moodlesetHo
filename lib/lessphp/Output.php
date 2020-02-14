<?php


class Less_Output{

	
	protected $strs = array();

	
	public function add($chunk, $fileInfo = null, $index = 0, $mapLines = null){
		$this->strs[] = $chunk;
	}

	
	public function isEmpty(){
		return count($this->strs) === 0;
	}


	
	public function toString(){
		return implode('',$this->strs);
	}

}