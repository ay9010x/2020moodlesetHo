<?php



namespace core\lock;

defined('MOODLE_INTERNAL') || die();


class lock {

    
    protected $key = '';

    
    protected $factory;

    
    protected $released;

    
    protected $caller = 'unknown';

    
    public function __construct($key, $factory) {
        $this->factory = $factory;
        $this->key = $key;
        $this->released = false;
        $caller = debug_backtrace(true, 2)[1];
        if ($caller && array_key_exists('file', $caller ) ) {
            $this->caller = $caller['file'] . ' on line ' . $caller['line'];
        } else if ($caller && array_key_exists('class', $caller)) {
            $this->caller = $caller['class'] . $caller['type'] . $caller['function'];
        }
    }

    
    public function get_key() {
        return $this->key;
    }

    
    public function extend($maxlifetime = 86400) {
        if ($this->factory) {
            return $this->factory->extend_lock($this, $maxlifetime);
        }
        return false;
    }

    
    public function release() {
        $this->released = true;
        if (empty($this->factory)) {
            return false;
        }
        $result = $this->factory->release_lock($this);
                unset($this->factory);
        $this->factory = null;
        $this->key = '';
        return $result;
    }

    
    public function __destruct() {
        if (!$this->released && defined('PHPUNIT_TEST')) {
            $key = $this->key;
            $this->release();
            throw new \coding_exception("A lock was created but not released at:\n" .
                                        $this->caller . "\n\n" .
                                        " Code should look like:\n\n" .
                                        " \$factory = \core\lock\lock_config::get_lock_factory('type');\n" .
                                        " \$lock = \$factory->get_lock($key);\n" .
                                        " \$lock->release();  // Locks must ALWAYS be released like this.\n\n");
        }
    }

}
