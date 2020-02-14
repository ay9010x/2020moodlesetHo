<?php



namespace core\lock;

defined('MOODLE_INTERNAL') || die();


class postgres_lock_factory implements lock_factory {

    
    protected $dblockid = -1;

    
    protected static $lockidcache = array();

    
    protected $db;

    
    protected $type;

    
    protected $openlocks = array();

    
    protected function get_unique_db_instance_id() {
        global $CFG;

        $strkey = $CFG->dbname . ':' . $CFG->prefix;
        $intkey = crc32($strkey);
                if (PHP_INT_SIZE == 8) {
            if ($intkey > 0x7FFFFFFF) {
                $intkey -= 0x100000000;
            }
        }

        return $intkey;
    }

    
    public function __construct($type) {
        global $DB;

        $this->type = $type;
        $this->dblockid = $this->get_unique_db_instance_id();
                $this->db = $DB;

        \core_shutdown_manager::register_function(array($this, 'auto_release'));
    }

    
    public function is_available() {
        return $this->db->get_dbfamily() === 'postgres';
    }

    
    public function supports_timeout() {
        return true;
    }

    
    public function supports_auto_release() {
        return true;
    }

    
    public function supports_recursion() {
        return true;
    }

    
    protected function get_index_from_key($key) {
        if (isset(self::$lockidcache[$key])) {
            return self::$lockidcache[$key];
        }

        $index = 0;
        $record = $this->db->get_record('lock_db', array('resourcekey' => $key));
        if ($record) {
            $index = $record->id;
        }

        if (!$index) {
            $record = new \stdClass();
            $record->resourcekey = $key;
            try {
                $index = $this->db->insert_record('lock_db', $record);
            } catch (\dml_exception $de) {
                                $record = $this->db->get_record('lock_db', array('resourcekey' => $key));
                if ($record) {
                    $index = $record->id;
                }
            }
        }

        if (!$index) {
            throw new \moodle_exception('Could not generate unique index for key');
        }

        self::$lockidcache[$key] = $index;
        return $index;
    }

    
    public function get_lock($resource, $timeout, $maxlifetime = 86400) {
        $giveuptime = time() + $timeout;

        $token = $this->get_index_from_key($resource);

        $params = array('locktype' => $this->dblockid,
                        'token' => $token);

        $locked = false;

        do {
            $result = $this->db->get_record_sql('SELECT pg_try_advisory_lock(:locktype, :token) AS locked', $params);
            $locked = $result->locked === 't';
            if (!$locked) {
                usleep(rand(10000, 250000));             }
                    } while (!$locked && time() < $giveuptime);

        if ($locked) {
            $this->openlocks[$token] = 1;
            return new lock($token, $this);
        }
        return false;
    }

    
    public function release_lock(lock $lock) {
        $params = array('locktype' => $this->dblockid,
                        'token' => $lock->get_key());
        $result = $this->db->get_record_sql('SELECT pg_advisory_unlock(:locktype, :token) AS unlocked', $params);
        $result = $result->unlocked === 't';
        if ($result) {
            unset($this->openlocks[$lock->get_key()]);
        }
        return $result;
    }

    
    public function extend_lock(lock $lock, $maxlifetime = 86400) {
                return false;
    }

    
    public function auto_release() {
                foreach ($this->openlocks as $key => $unused) {
            $lock = new lock($key, $this);
            $lock->release();
        }
    }

}
