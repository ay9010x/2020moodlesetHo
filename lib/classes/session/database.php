<?php



namespace core\session;

defined('MOODLE_INTERNAL') || die();


class database extends handler {
    
    protected $recordid = null;

    
    protected $database = null;

    
    protected $failed = false;

    
    protected $lasthash = null;

    
    protected $acquiretimeout = 120;

    
    public function __construct() {
        global $DB, $CFG;
                $this->database = $DB;

        if (!empty($CFG->session_database_acquire_lock_timeout)) {
            $this->acquiretimeout = (int)$CFG->session_database_acquire_lock_timeout;
        }
    }

    
    public function init() {
        if (!$this->database->session_lock_supported()) {
            throw new exception('sessionhandlerproblem', 'error', '', null, 'Database does not support session locking');
        }

        $result = session_set_save_handler(array($this, 'handler_open'),
            array($this, 'handler_close'),
            array($this, 'handler_read'),
            array($this, 'handler_write'),
            array($this, 'handler_destroy'),
            array($this, 'handler_gc'));
        if (!$result) {
            throw new exception('dbsessionhandlerproblem', 'error');
        }
    }

    
    public function session_exists($sid) {
                return true;
    }

    
    public function kill_all_sessions() {
                return;
    }

    
    public function kill_session($sid) {
                return;
    }

    
    public function handler_open($save_path, $session_name) {
                return true;
    }

    
    public function handler_close() {
        if ($this->recordid) {
            try {
                $this->database->release_session_lock($this->recordid);
            } catch (\Exception $ex) {
                            }
        }
        $this->recordid = null;
        $this->lasthash = null;
        return true;
    }

    
    public function handler_read($sid) {
        try {
            if (!$record = $this->database->get_record('sessions', array('sid'=>$sid), 'id')) {
                                                $this->failed = false;
                $this->recordid = null;
                $this->lasthash = sha1('');
                return '';
            }
            if ($this->recordid and $this->recordid != $record->id) {
                error_log('Second session read with different record id detected, cannot read session');
                $this->failed = true;
                $this->recordid = null;
                return '';
            }
            if (!$this->recordid) {
                                $this->database->get_session_lock($record->id, $this->acquiretimeout);
                $this->recordid = $record->id;
            }
        } catch (\dml_sessionwait_exception $ex) {
                                                error_log('Cannot obtain session lock for sid: '.$sid);
            $this->failed = true;
            throw $ex;

        } catch (\Exception $ex) {
                        error_log('Unknown exception when starting database session : '.$sid.' - '.$ex->getMessage());
            $this->failed = true;
            $this->recordid = null;
            return '';
        }

                if (!$record = $this->database->get_record('sessions', array('id'=>$record->id), 'id, sessdata')) {
                        $this->failed = true;
            $this->recordid = null;
            return '';
        }
        $this->failed = false;

        if (is_null($record->sessdata)) {
            $data = '';
            $this->lasthash = sha1('');
        } else {
            $data = base64_decode($record->sessdata);
            $this->lasthash = sha1($record->sessdata);
        }

        return $data;
    }

    
    public function handler_write($sid, $session_data) {
        if ($this->failed) {
                        return false;
        }

        $sessdata = base64_encode($session_data);         $hash = sha1($sessdata);

        if ($hash === $this->lasthash) {
            return true;
        }

        try {
            if ($this->recordid) {
                $this->database->set_field('sessions', 'sessdata', $sessdata, array('id'=>$this->recordid));
            } else {
                                $this->database->set_field('sessions', 'sessdata', $sessdata, array('sid'=>$sid));
            }
        } catch (\Exception $ex) {
                        error_log('Unknown exception when writing database session data : '.$sid.' - '.$ex->getMessage());
        }

        return true;
    }

    
    public function handler_destroy($sid) {
        if (!$session = $this->database->get_record('sessions', array('sid'=>$sid), 'id, sid')) {
            if ($sid == session_id()) {
                $this->recordid = null;
                $this->lasthash = null;
            }
            return true;
        }

        if ($this->recordid and $session->id == $this->recordid) {
            try {
                $this->database->release_session_lock($this->recordid);
            } catch (\Exception $ex) {
                            }
            $this->recordid = null;
            $this->lasthash = null;
        }

        $this->database->delete_records('sessions', array('id'=>$session->id));

        return true;
    }

    
    public function handler_gc($ignored_maxlifetime) {
                if (!$stalelifetime = ini_get('session.gc_maxlifetime')) {
            return true;
        }
        $params = array('purgebefore' => (time() - $stalelifetime));
        $this->database->delete_records_select('sessions', 'userid = 0 AND timemodified < :purgebefore', $params);
        return true;
    }
}
