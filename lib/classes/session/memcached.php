<?php



namespace core\session;

defined('MOODLE_INTERNAL') || die();


class memcached extends handler {
    
    protected $savepath;
    
    protected $servers;
    
    protected $prefix;
    
    protected $acquiretimeout = 120;
    
    protected $lockexpire = 7200;

    
    public function __construct() {
        global $CFG;

        if (empty($CFG->session_memcached_save_path)) {
            $this->savepath = '';
        } else {
            $this->savepath =  $CFG->session_memcached_save_path;
        }

        if (empty($this->savepath)) {
            $this->servers = array();
        } else {
            $this->servers = util::connection_string_to_memcache_servers($this->savepath);
        }

        if (empty($CFG->session_memcached_prefix)) {
            $this->prefix = ini_get('memcached.sess_prefix');
        } else {
            $this->prefix = $CFG->session_memcached_prefix;
        }

        if (!empty($CFG->session_memcached_acquire_lock_timeout)) {
            $this->acquiretimeout = (int)$CFG->session_memcached_acquire_lock_timeout;
        }

        if (!empty($CFG->session_memcached_lock_expire)) {
            $this->lockexpire = (int)$CFG->session_memcached_lock_expire;
        }
    }

    
    public function start() {
                        
        $default = ini_get('max_execution_time');
        set_time_limit($this->acquiretimeout);

        $isnewsession = empty($_COOKIE[session_name()]);
        $starttimer = microtime(true);

        $result = parent::start();

                                                                        if (!$isnewsession && $result && count($_SESSION) == 0
            && (microtime(true) - $starttimer + 1) >= floatval($this->acquiretimeout)) {
            $result = false;
        }

        set_time_limit($default);
        return $result;
    }

    
    public function init() {
        if (!extension_loaded('memcached')) {
            throw new exception('sessionhandlerproblem', 'error', '', null, 'memcached extension is not loaded');
        }
        $version = phpversion('memcached');
        if (!$version or version_compare($version, '2.0') < 0) {
            throw new exception('sessionhandlerproblem', 'error', '', null, 'memcached extension version must be at least 2.0');
        }
        if (empty($this->savepath)) {
            throw new exception('sessionhandlerproblem', 'error', '', null, '$CFG->session_memcached_save_path must be specified in config.php');
        }

        ini_set('session.save_handler', 'memcached');
        ini_set('session.save_path', $this->savepath);
        ini_set('memcached.sess_prefix', $this->prefix);
        ini_set('memcached.sess_locking', '1'); 
                if (version_compare($version, '3.0.0-dev') >= 0) {
            ini_set('memcached.sess_lock_wait_max', $this->acquiretimeout * 1000);
        } else {
            ini_set('memcached.sess_lock_max_wait', $this->acquiretimeout);
        }

        ini_set('memcached.sess_lock_expire', $this->lockexpire);
    }

    
    public function session_exists($sid) {
        if (!$this->servers) {
            return false;
        }

                        
        foreach ($this->servers as $server) {
            list($host, $port) = $server;
            $memcached = new \Memcached();
            $memcached->addServer($host, $port);
            $value = $memcached->get($this->prefix . $sid);
            $memcached->quit();
            if ($value !== false) {
                return true;
            }
        }

        return false;
    }

    
    public function kill_all_sessions() {
        global $DB;
        if (!$this->servers) {
            return;
        }

                        
        $memcacheds = array();
        foreach ($this->servers as $server) {
            list($host, $port) = $server;
            $memcached = new \Memcached();
            $memcached->addServer($host, $port);
            $memcacheds[] = $memcached;
        }

                
        $rs = $DB->get_recordset('sessions', array(), 'id DESC', 'id, sid');
        foreach ($rs as $record) {
            foreach ($memcacheds as $memcached) {
                $memcached->delete($this->prefix . $record->sid);
            }
        }
        $rs->close();

        foreach ($memcacheds as $memcached) {
            $memcached->quit();
        }
    }

    
    public function kill_session($sid) {
        if (!$this->servers) {
            return;
        }

                        
        foreach ($this->servers as $server) {
            list($host, $port) = $server;
            $memcached = new \Memcached();
            $memcached->addServer($host, $port);
            $memcached->delete($this->prefix . $sid);
            $memcached->quit();
        }
    }

}
