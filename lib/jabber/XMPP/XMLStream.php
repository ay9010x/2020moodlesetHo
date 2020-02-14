<?php



require_once dirname(__FILE__) . '/Exception.php';


require_once dirname(__FILE__) . '/XMLObj.php';


require_once dirname(__FILE__) . '/Log.php';


class XMPPHP_XMLStream {
	
	protected $socket;
	
	protected $parser;
	
	protected $buffer;
	
	protected $xml_depth = 0;
	
	protected $host;
	
	protected $port;
	
	protected $stream_start = '<stream>';
	
	protected $stream_end = '</stream>';
	
	protected $disconnected = false;
	
	protected $sent_disconnect = false;
	
	protected $ns_map = array();
	
	protected $current_ns = array();
	
	protected $xmlobj = null;
	
	protected $nshandlers = array();
	
	protected $xpathhandlers = array();
	
	protected $idhandlers = array();
	
	protected $eventhandlers = array();
	
	protected $lastid = 0;
	
	protected $default_ns;
	
	protected $until = '';
	
	protected $until_count = '';
	
	protected $until_happened = false;
	
	protected $until_payload = array();
	
	protected $log;
	
	protected $reconnect = true;
	
	protected $been_reset = false;
	
	protected $is_server;
	
	protected $last_send = 0;
	
	protected $use_ssl = false;
	
	protected $reconnectTimeout = 30;

	
	public function __construct($host = null, $port = null, $printlog = false, $loglevel = null, $is_server = false) {
		$this->reconnect = !$is_server;
		$this->is_server = $is_server;
		$this->host = $host;
		$this->port = $port;
		$this->setupParser();
		$this->log = new XMPPHP_Log($printlog, $loglevel);
	}

	
	public function __destruct() {
		if(!$this->disconnected && $this->socket) {
			$this->disconnect();
		}
	}
	
	
	public function getLog() {
		return $this->log;
	}
	
	
	public function getId() {
		$this->lastid++;
		return $this->lastid;
	}

	
	public function useSSL($use=true) {
		$this->use_ssl = $use;
	}

	
	public function addIdHandler($id, $pointer, $obj = null) {
		$this->idhandlers[$id] = array($pointer, $obj);
	}

	
	public function addHandler($name, $ns, $pointer, $obj = null, $depth = 1) {
				$this->nshandlers[] = array($name,$ns,$pointer,$obj, $depth);
	}

	
	public function addXPathHandler($xpath, $pointer, $obj = null) {
		if (preg_match_all("/\(?{[^\}]+}\)?(\/?)[^\/]+/", $xpath, $regs)) {
			$ns_tags = $regs[0];
		} else {
			$ns_tags = array($xpath);
		}
		foreach($ns_tags as $ns_tag) {
			list($l, $r) = explode("}", $ns_tag);
			if ($r != null) {
				$xpart = array(substr($l, 1), $r);
			} else {
				$xpart = array(null, $l);
			}
			$xpath_array[] = $xpart;
		}
		$this->xpathhandlers[] = array($xpath_array, $pointer, $obj);
	}

	
	public function addEventHandler($name, $pointer, $obj) {
		$this->eventhandlers[] = array($name, $pointer, $obj);
	}

	
	public function connect($timeout = 30, $persistent = false, $sendinit = true) {
		$this->sent_disconnect = false;
		$starttime = time();
		
		do {
			$this->disconnected = false;
			$this->sent_disconnect = false;
			if($persistent) {
				$conflag = STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT;
			} else {
				$conflag = STREAM_CLIENT_CONNECT;
			}
			$conntype = 'tcp';
			if($this->use_ssl) $conntype = 'ssl';
			$this->log->log("Connecting to $conntype://{$this->host}:{$this->port}");
			try {
				$this->socket = @stream_socket_client("$conntype://{$this->host}:{$this->port}", $errno, $errstr, $timeout, $conflag);
			} catch (Exception $e) {
				throw new XMPPHP_Exception($e->getMessage());
			}
			if(!$this->socket) {
				$this->log->log("Could not connect.",  XMPPHP_Log::LEVEL_ERROR);
				$this->disconnected = true;
								sleep(min($timeout, 5));
			}
		} while (!$this->socket && (time() - $starttime) < $timeout);
		
		if ($this->socket) {
			stream_set_blocking($this->socket, 1);
			if($sendinit) $this->send($this->stream_start);
		} else {
			throw new XMPPHP_Exception("Could not connect before timeout.");
		}
	}

	
	public function doReconnect() {
		if(!$this->is_server) {
			$this->log->log("Reconnecting ($this->reconnectTimeout)...",  XMPPHP_Log::LEVEL_WARNING);
			$this->connect($this->reconnectTimeout, false, false);
			$this->reset();
			$this->event('reconnect');
		}
	}

	public function setReconnectTimeout($timeout) {
		$this->reconnectTimeout = $timeout;
	}
	
	
	public function disconnect() {
		$this->log->log("Disconnecting...",  XMPPHP_Log::LEVEL_VERBOSE);
		if(false == (bool) $this->socket) {
			return;
		}
		$this->reconnect = false;
		$this->send($this->stream_end);
		$this->sent_disconnect = true;
		$this->processUntil('end_stream', 5);
		$this->disconnected = true;
	}

	
	public function isDisconnected() {
		return $this->disconnected;
	}

	
	
	private function __process($maximum=5) {
		
		$remaining = $maximum;
		
		do {
			$starttime = (microtime(true) * 1000000);
			$read = array($this->socket);
			$write = array();
			$except = array();
			if (is_null($maximum)) {
				$secs = NULL;
				$usecs = NULL;
			} else if ($maximum == 0) {
				$secs = 0;
				$usecs = 0;
			} else {
				$usecs = $remaining % 1000000;
				$secs = floor(($remaining - $usecs) / 1000000);
			}
			$updated = @stream_select($read, $write, $except, $secs, $usecs);
			if ($updated === false) {
				$this->log->log("Error on stream_select()",  XMPPHP_Log::LEVEL_VERBOSE);				
				if ($this->reconnect) {
					$this->doReconnect();
				} else {
					fclose($this->socket);
					$this->socket = NULL;
					return false;
				}
			} else if ($updated > 0) {
								$buff = @fread($this->socket, 4096);
				if(!$buff) { 
					if($this->reconnect) {
						$this->doReconnect();
					} else {
						fclose($this->socket);
						$this->socket = NULL;
						return false;
					}
				}
				$this->log->log("RECV: $buff",  XMPPHP_Log::LEVEL_VERBOSE);
				xml_parse($this->parser, $buff, false);
			} else {
							}
			$endtime = (microtime(true)*1000000);
			$time_past = $endtime - $starttime;
			$remaining = $remaining - $time_past;
		} while (is_null($maximum) || $remaining > 0);
		return true;
	}
	
	
	public function process() {
		$this->__process(NULL);
	}

	
	public function processTime($timeout=NULL) {
		if (is_null($timeout)) {
			return $this->__process(NULL);
		} else {
			return $this->__process($timeout * 1000000);
		}
	}

	
	public function processUntil($event, $timeout=-1) {
		$start = time();
		if(!is_array($event)) $event = array($event);
		$this->until[] = $event;
		end($this->until);
		$event_key = key($this->until);
		reset($this->until);
		$this->until_count[$event_key] = 0;
		$updated = '';
		while(!$this->disconnected and $this->until_count[$event_key] < 1 and (time() - $start < $timeout or $timeout == -1)) {
			$this->__process();
		}
		if(array_key_exists($event_key, $this->until_payload)) {
			$payload = $this->until_payload[$event_key];
			unset($this->until_payload[$event_key]);
			unset($this->until_count[$event_key]);
			unset($this->until[$event_key]);
		} else {
			$payload = array();
		}
		return $payload;
	}

	
	public function Xapply_socket($socket) {
		$this->socket = $socket;
	}

	
	public function startXML($parser, $name, $attr) {
		if($this->been_reset) {
			$this->been_reset = false;
			$this->xml_depth = 0;
		}
		$this->xml_depth++;
		if(array_key_exists('XMLNS', $attr)) {
			$this->current_ns[$this->xml_depth] = $attr['XMLNS'];
		} else {
			$this->current_ns[$this->xml_depth] = $this->current_ns[$this->xml_depth - 1];
			if(!$this->current_ns[$this->xml_depth]) $this->current_ns[$this->xml_depth] = $this->default_ns;
		}
		$ns = $this->current_ns[$this->xml_depth];
		foreach($attr as $key => $value) {
			if(strstr($key, ":")) {
				$key = explode(':', $key);
				$key = $key[1];
				$this->ns_map[$key] = $value;
			}
		}
		if(!strstr($name, ":") === false)
		{
			$name = explode(':', $name);
			$ns = $this->ns_map[$name[0]];
			$name = $name[1];
		}
		$obj = new XMPPHP_XMLObj($name, $ns, $attr);
		if($this->xml_depth > 1) {
			$this->xmlobj[$this->xml_depth - 1]->subs[] = $obj;
		}
		$this->xmlobj[$this->xml_depth] = $obj;
	}

	
	public function endXML($parser, $name) {
						if($this->been_reset) {
			$this->been_reset = false;
			$this->xml_depth = 0;
		}
		$this->xml_depth--;
		if($this->xml_depth == 1) {
									foreach($this->xpathhandlers as $handler) {
				if (is_array($this->xmlobj) && array_key_exists(2, $this->xmlobj)) {
					$searchxml = $this->xmlobj[2];
					$nstag = array_shift($handler[0]);
					if (($nstag[0] == null or $searchxml->ns == $nstag[0]) and ($nstag[1] == "*" or $nstag[1] == $searchxml->name)) {
						foreach($handler[0] as $nstag) {
							if ($searchxml !== null and $searchxml->hasSub($nstag[1], $ns=$nstag[0])) {
								$searchxml = $searchxml->sub($nstag[1], $ns=$nstag[0]);
							} else {
								$searchxml = null;
								break;
							}
						}
						if ($searchxml !== null) {
							if($handler[2] === null) $handler[2] = $this;
							$this->log->log("Calling {$handler[1]}",  XMPPHP_Log::LEVEL_DEBUG);
							$handler[2]->{$handler[1]}($this->xmlobj[2]);
						}
					}
				}
			}
			foreach($this->nshandlers as $handler) {
				if($handler[4] != 1 and array_key_exists(2, $this->xmlobj) and  $this->xmlobj[2]->hasSub($handler[0])) {
					$searchxml = $this->xmlobj[2]->sub($handler[0]);
				} elseif(is_array($this->xmlobj) and array_key_exists(2, $this->xmlobj)) {
					$searchxml = $this->xmlobj[2];
				}
				if($searchxml !== null and $searchxml->name == $handler[0] and ($searchxml->ns == $handler[1] or (!$handler[1] and $searchxml->ns == $this->default_ns))) {
					if($handler[3] === null) $handler[3] = $this;
					$this->log->log("Calling {$handler[2]}",  XMPPHP_Log::LEVEL_DEBUG);
					$handler[3]->{$handler[2]}($this->xmlobj[2]);
				}
			}
			foreach($this->idhandlers as $id => $handler) {
				if(array_key_exists('id', $this->xmlobj[2]->attrs) and $this->xmlobj[2]->attrs['id'] == $id) {
					if($handler[1] === null) $handler[1] = $this;
					$handler[1]->{$handler[0]}($this->xmlobj[2]);
										unset($this->idhandlers[$id]);
					break;
				}
			}
			if(is_array($this->xmlobj)) {
				$this->xmlobj = array_slice($this->xmlobj, 0, 1);
				if(isset($this->xmlobj[0]) && $this->xmlobj[0] instanceof XMPPHP_XMLObj) {
					$this->xmlobj[0]->subs = null;
				}
			}
			unset($this->xmlobj[2]);
		}
		if($this->xml_depth == 0 and !$this->been_reset) {
			if(!$this->disconnected) {
				if(!$this->sent_disconnect) {
					$this->send($this->stream_end);
				}
				$this->disconnected = true;
				$this->sent_disconnect = true;
				fclose($this->socket);
				if($this->reconnect) {
					$this->doReconnect();
				}
			}
			$this->event('end_stream');
		}
	}

	
	public function charXML($parser, $data) {
		if(array_key_exists($this->xml_depth, $this->xmlobj)) {
			$this->xmlobj[$this->xml_depth]->data .= $data;
		}
	}

	
	public function event($name, $payload = null) {
		$this->log->log("EVENT: $name",  XMPPHP_Log::LEVEL_DEBUG);
		foreach($this->eventhandlers as $handler) {
			if($name == $handler[0]) {
				if($handler[2] === null) {
					$handler[2] = $this;
				}
				$handler[2]->{$handler[1]}($payload);
			}
		}
		foreach($this->until as $key => $until) {
			if(is_array($until)) {
				if(in_array($name, $until)) {
					$this->until_payload[$key][] = array($name, $payload);
					if(!isset($this->until_count[$key])) {
						$this->until_count[$key] = 0;
					}
					$this->until_count[$key] += 1;
									}
			}
		}
	}

	
	public function read() {
		$buff = @fread($this->socket, 1024);
		if(!$buff) { 
			if($this->reconnect) {
				$this->doReconnect();
			} else {
				fclose($this->socket);
				return false;
			}
		}
		$this->log->log("RECV: $buff",  XMPPHP_Log::LEVEL_VERBOSE);
		xml_parse($this->parser, $buff, false);
	}

	
	public function send($msg, $timeout=NULL) {

		if (is_null($timeout)) {
			$secs = NULL;
			$usecs = NULL;
		} else if ($timeout == 0) {
			$secs = 0;
			$usecs = 0;
		} else {
			$maximum = $timeout * 1000000;
			$usecs = $maximum % 1000000;
			$secs = floor(($maximum - $usecs) / 1000000);
		}
		
		$read = array();
		$write = array($this->socket);
		$except = array();
		
		$select = @stream_select($read, $write, $except, $secs, $usecs);
		
		if($select === False) {
			$this->log->log("ERROR sending message; reconnecting.");
			$this->doReconnect();
						return false;
		} elseif ($select > 0) {
			$this->log->log("Socket is ready; send it.", XMPPHP_Log::LEVEL_VERBOSE);
		} else {
			$this->log->log("Socket is not ready; break.", XMPPHP_Log::LEVEL_ERROR);
			return false;
		}
		
		$sentbytes = @fwrite($this->socket, $msg);
		$this->log->log("SENT: " . mb_substr($msg, 0, $sentbytes, '8bit'), XMPPHP_Log::LEVEL_VERBOSE);
		if($sentbytes === FALSE) {
			$this->log->log("ERROR sending message; reconnecting.", XMPPHP_Log::LEVEL_ERROR);
			$this->doReconnect();
			return false;
		}
		$this->log->log("Successfully sent $sentbytes bytes.", XMPPHP_Log::LEVEL_VERBOSE);
		return $sentbytes;
	}

	public function time() {
		list($usec, $sec) = explode(" ", microtime());
		return (float)$sec + (float)$usec;
	}

	
	public function reset() {
		$this->xml_depth = 0;
		unset($this->xmlobj);
		$this->xmlobj = array();
		$this->setupParser();
		if(!$this->is_server) {
			$this->send($this->stream_start);
		}
		$this->been_reset = true;
	}

	
	public function setupParser() {
		$this->parser = xml_parser_create('UTF-8');
		xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, 'startXML', 'endXML');
		xml_set_character_data_handler($this->parser, 'charXML');
	}

	public function readyToProcess() {
		$read = array($this->socket);
		$write = array();
		$except = array();
		$updated = @stream_select($read, $write, $except, 0);
		return (($updated !== false) && ($updated > 0));
	}
}