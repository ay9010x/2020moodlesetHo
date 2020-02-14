<?php



namespace logstore_database;
defined('MOODLE_INTERNAL') || die();



class helper {
    
    public static function get_drivers() {
        return array(
            ''               => get_string('choosedots'),
            'native/mysqli'  => \moodle_database::get_driver_instance('mysqli', 'native')->get_name(),
            'native/mariadb' => \moodle_database::get_driver_instance('mariadb', 'native')->get_name(),
            'native/pgsql'   => \moodle_database::get_driver_instance('pgsql', 'native')->get_name(),
            'native/oci'     => \moodle_database::get_driver_instance('oci', 'native')->get_name(),
            'native/sqlsrv'  => \moodle_database::get_driver_instance('sqlsrv', 'native')->get_name(),
            'native/mssql'   => \moodle_database::get_driver_instance('mssql', 'native')->get_name()
        );
    }

    
    public static function get_level_options() {
        return array(
            \core\event\base::LEVEL_TEACHING      => get_string('teaching', 'logstore_database'),
            \core\event\base::LEVEL_PARTICIPATING => get_string('participating', 'logstore_database'),
            \core\event\base::LEVEL_OTHER         => get_string('other', 'logstore_database'),
        );
    }

    
    public static function get_action_options() {
        return array(
            'c' => get_string('create', 'logstore_database'),
            'r' => get_string('read', 'logstore_database'),
            'u' => get_string('update', 'logstore_database'),
            'd' => get_string('delete')
        );
    }
}
