<?php



class XMPPHP_Log {
	
	const LEVEL_ERROR   = 0;
	const LEVEL_WARNING = 1;
	const LEVEL_INFO	= 2;
	const LEVEL_DEBUG   = 3;
	const LEVEL_VERBOSE = 4;
	
	
	protected $data = array();

	
	protected $names = array('ERROR', 'WARNING', 'INFO', 'DEBUG', 'VERBOSE');

	
	protected $runlevel;

	
	protected $printout;

	
	public function __construct($printout = false, $runlevel = self::LEVEL_INFO) {
		$this->printout = (boolean)$printout;
		$this->runlevel = (int)$runlevel;
	}

	
	public function log($msg, $runlevel = self::LEVEL_INFO) {
		$time = time();
				if($this->printout and $runlevel <= $this->runlevel) {
			$this->writeLine($msg, $runlevel, $time);
		}
	}

	
	public function printout($clear = true, $runlevel = null) {
		if($runlevel === null) {
			$runlevel = $this->runlevel;
		}
		foreach($this->data as $data) {
			if($runlevel <= $data[0]) {
				$this->writeLine($data[1], $runlevel, $data[2]);
			}
		}
		if($clear) {
			$this->data = array();
		}
	}
	
	protected function writeLine($msg, $runlevel, $time) {
				echo $time." [".$this->names[$runlevel]."]: ".$msg."\n";
		flush();
	}
}
