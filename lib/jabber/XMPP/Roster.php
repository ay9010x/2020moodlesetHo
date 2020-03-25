<?php




class Roster {
	
	protected $roster_array = array();
	
	public function __construct($roster_array = array()) {
		if ($this->verifyRoster($roster_array)) {
			$this->roster_array = $roster_array; 		} else {
			$this->roster_array = array();
		}
	}

	
	protected function verifyRoster($roster_array) {
				return True;
	}

	
	public function addContact($jid, $subscription, $name='', $groups=array()) {
		$contact = array('jid' => $jid, 'subscription' => $subscription, 'name' => $name, 'groups' => $groups);
		if ($this->isContact($jid)) {
			$this->roster_array[$jid]['contact'] = $contact;
		} else {
			$this->roster_array[$jid] = array('contact' => $contact);
		}
	}

	
	public function getContact($jid) {
		if ($this->isContact($jid)) {
			return $this->roster_array[$jid]['contact'];
		}
	}

	
	public function isContact($jid) {
		return (array_key_exists($jid, $this->roster_array));
	}

	
	public function setPresence($presence, $priority, $show, $status) {
		list($jid, $resource) = explode("/", $presence);
		if ($show != 'unavailable') {
			if (!$this->isContact($jid)) {
				$this->addContact($jid, 'not-in-roster');
			}
			$resource = $resource ? $resource : '';
			$this->roster_array[$jid]['presence'][$resource] = array('priority' => $priority, 'show' => $show, 'status' => $status);
		} else { 			unset($this->roster_array[$jid]['resource'][$resource]);
		}
	}

	
	public function getPresence($jid) {
		$split = explode("/", $jid);
		$jid = $split[0];
		if($this->isContact($jid)) {
			$current = array('resource' => '', 'active' => '', 'priority' => -129, 'show' => '', 'status' => ''); 			foreach($this->roster_array[$jid]['presence'] as $resource => $presence) {
								if ($presence['priority'] > $current['priority'] and (($presence['show'] == "chat" or $presence['show'] == "available") or ($current['show'] != "chat" or $current['show'] != "available"))) {
					$current = $presence;
					$current['resource'] = $resource;
				}
			}
			return $current;
		}
	}
	
	public function getRoster() {
		return $this->roster_array;
	}
}
?>
