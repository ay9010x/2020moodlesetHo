<?php



namespace core\lock;

defined('MOODLE_INTERNAL') || die();


class db_record_lock_factory implements lock_factory {

    
    protected $db;

    
    protected $type;

    
    protected $openlocks = array();

    
    public function is_available() {
        return true;
    }

    
    public function __construct($type) {
        global $DB;

        $this->type = $type;
                $this->db = $DB;

        \core_shutdown_manager::register_function(array($this, 'auto_release'));
    }

    
    public function supports_timeout() {
        return true;
    }

    
    public function supports_auto_release() {
        return true;
    }

    
    public function supports_recursion() {
        return false;
    }

    
    protected function generate_unique_token() {
        return generate_uuid();
    }

    
    public function get_lock($resource, $timeout, $maxlifetime = 86400) {

        $token = $this->generate_unique_token();
        $now = time();
        $giveuptime = $now + $timeout;
        $expires = $now + $maxlifetime;

        if (!$this->db->record_exists('lock_db', array('resourcekey' => $resource))) {
            $record = new \stdClass();
            $record->resourcekey = $resource;
            $result = $this->db->insert_record('lock_db', $record);
        }

        $params = array('expires' => $expires,
                        'token' => $token,
                        'resourcekey' => $resource,
                        'now' => $now);
        $sql = 'UPDATE {lock_db}
                   SET
                       expires = :expires,
                       owner = :token
                 WHERE
                       resourcekey = :resourcekey AND
                       (owner IS NULL OR expires < :now)';

        do {
            $now = time();
            $params['now'] = $now;
            $this->db->execute($sql, $params);

            $countparams = array('owner' => $token, 'resourcekey' => $resource);
            $result = $this->db->count_records('lock_db', $countparams);
            $locked = $result === 1;
            if (!$locked) {
                usleep(rand(10000, 250000));             }
                    } while (!$locked && $now < $giveuptime);

        if ($locked) {
            $this->openlocks[$token] = 1;
            return new lock($token, $this);
        }

        return false;
    }

    
    public function release_lock(lock $lock) {
        $params = array('noexpires' => null,
                        'token' => $lock->get_key(),
                        'noowner' => null);

        $sql = 'UPDATE {lock_db}
                    SET
                        expires = :noexpires,
                        owner = :noowner
                    WHERE
                        owner = :token';
        $result = $this->db->execute($sql, $params);
        if ($result) {
            unset($this->openlocks[$lock->get_key()]);
        }
        return $result;
    }

    
    public function extend_lock(lock $lock, $maxlifetime = 86400) {
        $now = time();
        $expires = $now + $maxlifetime;
        $params = array('expires' => $expires,
                        'token' => $lock->get_key());

        $sql = 'UPDATE {lock_db}
                    SET
                        expires = :expires,
                    WHERE
                        owner = :token';

        $this->db->execute($sql, $params);
        $countparams = array('owner' => $lock->get_key());
        $result = $this->count_records('lock_db', $countparams);

        return $result === 0;
    }

    
    public function auto_release() {
                foreach ($this->openlocks as $key => $unused) {
            $lock = new lock($key, $this);
            $lock->release();
        }
    }
}
