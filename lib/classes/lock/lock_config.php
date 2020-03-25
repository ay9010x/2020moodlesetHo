<?php



namespace core\lock;

defined('MOODLE_INTERNAL') || die();


class lock_config {

    
    public static function get_lock_factory($type) {
        global $CFG, $DB;
        $lockfactory = null;

        if (isset($CFG->lock_factory) && $CFG->lock_factory != 'auto') {
            if (!class_exists($CFG->lock_factory)) {
                                                throw new \coding_exception('Lock factory set in $CFG does not exist: ' . $CFG->lock_factory);
            }
            $lockfactoryclass = $CFG->lock_factory;
            $lockfactory = new $lockfactoryclass($type);
        } else {
            $dbtype = clean_param($DB->get_dbfamily(), PARAM_ALPHA);

                        $lockfactoryclass = "\\core\\lock\\${dbtype}_lock_factory";
            if (!class_exists($lockfactoryclass)) {
                $lockfactoryclass = '\core\lock\file_lock_factory';
            }
            
            $lockfactory = new $lockfactoryclass($type);
            if (!$lockfactory->is_available()) {
                                $lockfactory = new \core\lock\db_record_lock_factory($type);
            }
        }

        return $lockfactory;
    }

}
