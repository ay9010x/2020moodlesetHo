<?php



namespace core\session;

use RedisException;

defined('MOODLE_INTERNAL') || die();


class redis extends handler {
    
    protected $host = '';
    
    protected $port = 6379;
    
    protected $database = 0;
    
    protected $prefix = '';
    
    protected $acquiretimeout = 120;
    
    protected $lockexpire;

    
    protected $connection = null;

    
    protected $locks = array();

    
    protected $timeout;

    
    public function __construct() {
        global $CFG;

        if (isset($CFG->session_redis_host)) {
            $this->host = $CFG->session_redis_host;
        }

        if (isset($CFG->session_redis_port)) {
            $this->port = (int)$CFG->session_redis_port;
        }

        if (isset($CFG->session_redis_database)) {
            $this->database = (int)$CFG->session_redis_database;
        }

        if (isset($CFG->session_redis_prefix)) {
            $this->prefix = $CFG->session_redis_prefix;
        }

        if (isset($CFG->session_redis_acquire_lock_timeout)) {
            $this->acquiretimeout = (int)$CFG->session_redis_acquire_lock_timeout;
        }

                                $updatefreq = empty($CFG->session_update_timemodified_frequency) ? 20 : $CFG->session_update_timemodified_frequency;
        $this->timeout = $CFG->sessiontimeout + $updatefreq + MINSECS;

        $this->lockexpire = $CFG->sessiontimeout;
        if (isset($CFG->session_redis_lock_expire)) {
            $this->lockexpire = (int)$CFG->session_redis_lock_expire;
        }
    }

    
    public function start() {
        $result = parent::start();

        return $result;
    }

    
    public function init() {
        if (!extension_loaded('redis')) {
            throw new exception('sessionhandlerproblem', 'error', '', null, 'redis extension is not loaded');
        }

        if (empty($this->host)) {
            throw new exception('sessionhandlerproblem', 'error', '', null,
                    '$CFG->session_redis_host must be specified in config.php');
        }

                $version = phpversion('Redis');
        if (!$version or version_compare($version, '2.0') <= 0) {
            throw new exception('sessionhandlerproblem', 'error', '', null, 'redis extension version must be at least 2.0');
        }

        $this->connection = new \Redis();

        $result = session_set_save_handler(array($this, 'handler_open'),
            array($this, 'handler_close'),
            array($this, 'handler_read'),
            array($this, 'handler_write'),
            array($this, 'handler_destroy'),
            array($this, 'handler_gc'));
        if (!$result) {
            throw new exception('redissessionhandlerproblem', 'error');
        }

        try {
                        if (!$this->connection->connect($this->host, $this->port, 1)) {
                throw new RedisException('Unable to connect to host.');
            }
            if (!$this->connection->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP)) {
                throw new RedisException('Unable to set Redis PHP Serializer option.');
            }

            if ($this->prefix !== '') {
                                if (!$this->connection->setOption(\Redis::OPT_PREFIX, $this->prefix)) {
                    throw new RedisException('Unable to set Redis Prefix option.');
                }
            }
            if ($this->database !== 0) {
                if (!$this->connection->select($this->database)) {
                    throw new RedisException('Unable to select Redis database '.$this->database.'.');
                }
            }
            $this->connection->ping();
            return true;
        } catch (RedisException $e) {
            error_log('Failed to connect to redis at '.$this->host.':'.$this->port.', error returned was: '.$e->getMessage());
            return false;
        }
    }

    
    public function handler_open($savepath, $sessionname) {
        return true;
    }

    
    public function handler_close() {
        try {
            foreach ($this->locks as $id => $expirytime) {
                if ($expirytime > $this->time()) {
                    $this->unlock_session($id);
                }
                unset($this->locks[$id]);
            }
        } catch (RedisException $e) {
            error_log('Failed talking to redis: '.$e->getMessage());
            return false;
        }

        return true;
    }
    
    public function handler_read($id) {
        try {
            $this->lock_session($id);
            $sessiondata = $this->connection->get($id);
            if ($sessiondata === false) {
                $this->unlock_session($id);
                return '';
            }
            $this->connection->expire($id, $this->timeout);
        } catch (RedisException $e) {
            error_log('Failed talking to redis: '.$e->getMessage());
            throw $e;
        }
        return $sessiondata;
    }

    
    public function handler_write($id, $data) {
        if (is_null($this->connection)) {
                        error_log('Tried to write session: '.$id.' before open or after close.');
            return false;
        }

                                        try {
            $this->connection->setex($id, $this->timeout, $data);
        } catch (RedisException $e) {
            error_log('Failed talking to redis: '.$e->getMessage());
            return false;
        }
        return true;
    }

    
    public function handler_destroy($id) {
        try {
            $this->connection->del($id);
            $this->unlock_session($id);
        } catch (RedisException $e) {
            error_log('Failed talking to redis: '.$e->getMessage());
            return false;
        }

        return true;
    }

    
    public function handler_gc($maxlifetime) {
        return true;
    }

    
    protected function unlock_session($id) {
        if (isset($this->locks[$id])) {
            $this->connection->del($id.".lock");
            unset($this->locks[$id]);
        }
    }

    
    protected function lock_session($id) {
        $lockkey = $id.".lock";

        $haslock = isset($this->locks[$id]) && $this->time() < $this->locks[$id];
        $startlocktime = $this->time();

        
        while (!$haslock) {
            $haslock = $this->connection->setnx($lockkey, '1');
            if (!$haslock) {
                usleep(rand(100000, 1000000));
                if ($this->time() > $startlocktime + $this->acquiretimeout) {
                                                                                error_log('Cannot obtain session lock for sid: '.$id.' within '.$this->acquiretimeout.
                            '. It is likely another page has a long session lock, or the session lock was never released.');
                    throw new exception("Unable to obtain session lock");
                }
            } else {
                $this->locks[$id] = $this->time() + $this->lockexpire;
                $this->connection->expire($lockkey, $this->lockexpire);
                return true;
            }
        }
    }

    
    protected function time() {
        return time();
    }

    
    public function session_exists($sid) {
        if (!$this->connection) {
            return false;
        }

        try {
            return $this->connection->exists($sid);
        } catch (RedisException $e) {
            return false;
        }
    }

    
    public function kill_all_sessions() {
        global $DB;
        if (!$this->connection) {
            return;
        }

        $rs = $DB->get_recordset('sessions', array(), 'id DESC', 'id, sid');
        foreach ($rs as $record) {
            $this->handler_destroy($record->sid);
        }
        $rs->close();
    }

    
    public function kill_session($sid) {
        if (!$this->connection) {
            return;
        }

        $this->handler_destroy($sid);
    }
}