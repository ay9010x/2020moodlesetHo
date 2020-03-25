<?php



require_once(__DIR__.'/../lib.php');


class test_lock {

    
    protected static $lockhandles = array();

    
    public static function acquire($framework) {
        global $CFG;
        $datarootpath = $CFG->{$framework . '_dataroot'} . '/' . $framework;
        $lockfile = $datarootpath . '/lock';
        if (!file_exists($datarootpath)) {
                        return;
        }
        if (!file_exists($lockfile)) {
            file_put_contents($lockfile, 'This file prevents concurrent execution of Moodle ' . $framework . ' tests');
            testing_fix_file_permissions($lockfile);
        }
        if (self::$lockhandles[$framework] = fopen($lockfile, 'r')) {
            $wouldblock = null;
            $locked = flock(self::$lockhandles[$framework], (LOCK_EX | LOCK_NB), $wouldblock);
            if (!$locked) {
                if ($wouldblock) {
                    echo "Waiting for other test execution to complete...\n";
                }
                $locked = flock(self::$lockhandles[$framework], LOCK_EX);
            }
            if (!$locked) {
                fclose(self::$lockhandles[$framework]);
                self::$lockhandles[$framework] = null;
            }
        }
        register_shutdown_function(array('test_lock', 'release'), $framework);
    }

    
    public static function release($framework) {
        if (self::$lockhandles[$framework]) {
            flock(self::$lockhandles[$framework], LOCK_UN);
            fclose(self::$lockhandles[$framework]);
            self::$lockhandles[$framework] = null;
        }
    }

}
