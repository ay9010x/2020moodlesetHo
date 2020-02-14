<?php



namespace core\lock;

defined('MOODLE_INTERNAL') || die();


class file_lock_factory implements lock_factory {

    
    protected $type;

    
    protected $lockdirectory;

    
    protected $verbose;

    
    public function __construct($type) {
        global $CFG;

        $this->type = $type;
        if (!isset($CFG->file_lock_root)) {
            $this->lockdirectory = $CFG->dataroot . '/lock';
        } else {
            $this->lockdirectory = $CFG->file_lock_root;
        }
        $this->verbose = false;
        if ($CFG->debugdeveloper) {
            $this->verbose = true;
        }
    }

    
    public function supports_timeout() {
        global $CFG;

        return $CFG->ostype !== 'WINDOWS';
    }

    
    public function supports_auto_release() {
        return true;
    }

    
    public function is_available() {
        global $CFG;
        $preventfilelocking = !empty($CFG->preventfilelocking);
        $lockdirisdataroot = true;
        if (!empty($CFG->file_lock_root) && strpos($CFG->file_lock_root, $CFG->dataroot) !== 0) {
            $lockdirisdataroot = false;
        }
        return !$preventfilelocking || !$lockdirisdataroot;
    }

    
    public function supports_recursion() {
        return false;
    }

    
    protected function get_debug_info() {
        return 'host:' . php_uname('n') . ', pid:' . getmypid() . ', time:' . time();
    }

    
    public function get_lock($resource, $timeout, $maxlifetime = 86400) {
        $giveuptime = time() + $timeout;

        $hash = md5($this->type . '_' . $resource);
        $lockdir = $this->lockdirectory . '/' . substr($hash, 0, 2);

        if (!check_dir_exists($lockdir, true, true)) {
            return false;
        }

        $lockfilename = $lockdir . '/' . $hash;

        $filehandle = fopen($lockfilename, "wb");

                if (!$filehandle) {
            return false;
        }

        do {
                        $wouldblock = false;
            $locked = flock($filehandle, LOCK_EX | LOCK_NB, $wouldblock);
            if (!$locked && $wouldblock) {
                usleep(rand(10000, 250000));             }
                    } while (!$locked && $wouldblock && time() < $giveuptime);

        if (!$locked) {
            fclose($filehandle);
            return false;
        }
        if ($this->verbose) {
            fwrite($filehandle, $this->get_debug_info());
        }
        return new lock($filehandle, $this);
    }

    
    public function release_lock(lock $lock) {
        $handle = $lock->get_key();

        if (!$handle) {
                        return false;
        }

        $result = flock($handle, LOCK_UN);
        fclose($handle);
        return $result;
    }

    
    public function extend_lock(lock $lock, $maxlifetime = 86400) {
                return false;
    }

}
