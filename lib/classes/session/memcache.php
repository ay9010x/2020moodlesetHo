<?php



namespace core\session;

defined('MOODLE_INTERNAL') || die();


class memcache extends handler {
    
    protected $savepath;
    
    protected $servers;
    
    protected $acquiretimeout = 120;

    
    public function __construct() {
        global $CFG;

        if (empty($CFG->session_memcache_save_path)) {
            $this->savepath = '';
        } else {
            $this->savepath = $CFG->session_memcache_save_path;
        }

        if (empty($this->savepath)) {
            $this->servers = array();
        } else {
            $this->servers = util::connection_string_to_memcache_servers($this->savepath);
        }

        if (!empty($CFG->session_memcache_acquire_lock_timeout)) {
            $this->acquiretimeout = (int)$CFG->session_memcache_acquire_lock_timeout;
        }
    }

    
    public function start() {
        $default = ini_get('max_execution_time');
        set_time_limit($this->acquiretimeout);

        $result = parent::start();

        set_time_limit($default);
        return $result;
    }

    
    public function init() {
        if (!extension_loaded('memcache')) {
            throw new exception('sessionhandlerproblem', 'error', '', null,
                    'memcache extension is not loaded');
        }
        $version = phpversion('memcache');
        if (!$version or version_compare($version, '2.2') < 0) {
            throw new exception('sessionhandlerproblem', 'error', '', null,
                    'memcache extension version must be at least 2.2');
        }
        if (empty($this->savepath)) {
            throw new exception('sessionhandlerproblem', 'error', '', null,
                    '$CFG->session_memcache_save_path must be specified in config.php');
        }
                                        if (strpos($this->savepath, 'tcp://') !== false) {
            throw new exception('sessionhandlerproblem', 'error', '', null,
                    '$CFG->session_memcache_save_path should not contain tcp://');
        }

        ini_set('session.save_handler', 'memcache');

                                $memcacheformat = preg_replace('~(^|,\s*)~','$1tcp://', $this->savepath);
        ini_set('session.save_path', $memcacheformat);
    }

    
    public function session_exists($sid) {
        $result = false;

        foreach ($this->get_memcaches() as $memcache) {
            if ($result === false) {
                $value = $memcache->get($sid);
                if ($value !== false) {
                    $result = true;
                }
            }
            $memcache->close();
        }

        return $result;
    }

    
    protected function get_memcaches() {
        $result = array();
        foreach ($this->servers as $server) {
            $memcache = new \Memcache();
            $memcache->addServer($server[0], $server[1]);
            $result[] = $memcache;
        }
        return $result;
    }

    
    public function kill_all_sessions() {
        global $DB;
        if (!$this->servers) {
            return;
        }

        $memcaches = $this->get_memcaches();

                
        $rs = $DB->get_recordset('sessions', array(), 'id DESC', 'id, sid');
        foreach ($rs as $record) {
            foreach ($memcaches as $memcache) {
                $memcache->delete($record->sid);
            }
        }
        $rs->close();

        foreach ($memcaches as $memcache) {
            $memcache->close();
        }
    }

    
    public function kill_session($sid) {
        foreach ($this->get_memcaches() as $memcache) {
            $memcache->delete($sid);
            $memcache->close();
        }
    }
}
