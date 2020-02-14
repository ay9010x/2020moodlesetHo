<?php



namespace core\session;

defined('MOODLE_INTERNAL') || die();


class file extends handler {
    
    protected $sessiondir;

    
    public function __construct() {
        global $CFG;

        if (!empty($CFG->session_file_save_path)) {
            $this->sessiondir = $CFG->session_file_save_path;
        } else {
            $this->sessiondir = "$CFG->dataroot/sessions";
        }
    }

    
    public function init() {
        if (preg_match('/^[0-9]+;/', $this->sessiondir)) {
            throw new exception('sessionhandlerproblem', 'error', '', null, 'Multilevel session directories are not supported');
        }
                make_writable_directory($this->sessiondir, false);
        if (!is_writable($this->sessiondir)) {
            throw new exception('sessionhandlerproblem', 'error', '', null, 'Session directory is not writable');
        }
                        $freespace = @disk_free_space($this->sessiondir);
                if (!($freespace > 2048) and ($freespace !== false) and ($freespace !== null)) {
            throw new exception('sessiondiskfull', 'error');
        }

                ini_set('session.save_handler', 'files');
        ini_set('session.save_path', $this->sessiondir);
    }

    
    public function session_exists($sid) {
        $sid = clean_param($sid, PARAM_FILE);
        if (!$sid) {
            return false;
        }
        $sessionfile = "$this->sessiondir/sess_$sid";
        return file_exists($sessionfile);
    }

    
    public function kill_all_sessions() {
        if (is_dir($this->sessiondir)) {
            foreach (glob("$this->sessiondir/sess_*") as $filename) {
                @unlink($filename);
            }
        }
    }

    
    public function kill_session($sid) {
        $sid = clean_param($sid, PARAM_FILE);
        if (!$sid) {
            return;
        }
        $sessionfile = "$this->sessiondir/sess_$sid";
        if (file_exists($sessionfile)) {
            @unlink($sessionfile);
        }
    }
}
