<?php



namespace logstore_legacy\test;

defined('MOODLE_INTERNAL') || die();

class unittest_logstore_legacy extends \logstore_legacy\log\store {

    
    public static function replace_sql_legacy($select, array $params, $sort = '') {
        return parent::replace_sql_legacy($select, $params, $sort);
    }
}
