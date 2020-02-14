<?php 



class XMPPHP_XMLObj {
	
	public $name;
	
	
	public $ns;
	
	
	public $attrs = array();
	
	
	public $subs = array();
	
	
	public $data = '';

	
	public function __construct($name, $ns = '', $attrs = array(), $data = '') {
		$this->name = strtolower($name);
		$this->ns   = $ns;
		if(is_array($attrs) && count($attrs)) {
			foreach($attrs as $key => $value) {
				$this->attrs[strtolower($key)] = $value;
			}
		}
		$this->data = $data;
	}

	
	public function printObj($depth = 0) {
		print str_repeat("\t", $depth) . $this->name . " " . $this->ns . ' ' . $this->data;
		print "\n";
		foreach($this->subs as $sub) {
			$sub->printObj($depth + 1);
		}
	}

	
	public function toString($str = '') {
		$str .= "<{$this->name} xmlns='{$this->ns}' ";
		foreach($this->attrs as $key => $value) {
			if($key != 'xmlns') {
				$value = htmlspecialchars($value);
				$str .= "$key='$value' ";
			}
		}
		$str .= ">";
		foreach($this->subs as $sub) {
			$str .= $sub->toString();
		}
		$body = htmlspecialchars($this->data);
		$str .= "$body</{$this->name}>";
		return $str;
	}

	
	public function hasSub($name, $ns = null) {
		foreach($this->subs as $sub) {
			if(($name == "*" or $sub->name == $name) and ($ns == null or $sub->ns == $ns)) return true;
		}
		return false;
	}

	
	public function sub($name, $attrs = null, $ns = null) {
				foreach($this->subs as $sub) {
			if($sub->name == $name and ($ns == null or $sub->ns == $ns)) {
				return $sub;
			}
		}
	}
}
