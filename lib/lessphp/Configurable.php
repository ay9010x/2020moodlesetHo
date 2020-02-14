<?php


abstract class Less_Configurable {

	
	protected $options = array();

	
	protected $defaultOptions = array();


	
	public function setOptions($options){
		$options = array_intersect_key($options,$this->defaultOptions);
		$this->options = array_merge($this->defaultOptions, $this->options, $options);
	}


	
	public function getOption($name, $default = null){
		if(isset($this->options[$name])){
			return $this->options[$name];
		}
		return $default;
	}


	
	public function setOption($name, $value){
		$this->options[$name] = $value;
	}

}