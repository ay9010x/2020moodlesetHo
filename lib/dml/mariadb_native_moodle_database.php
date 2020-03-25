<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/moodle_database.php');
require_once(__DIR__.'/mysqli_native_moodle_database.php');
require_once(__DIR__.'/mysqli_native_moodle_recordset.php');
require_once(__DIR__.'/mysqli_native_moodle_temptables.php');


class mariadb_native_moodle_database extends mysqli_native_moodle_database {

    
    public function get_name() {
        return get_string('nativemariadb', 'install');
    }

    
    public function get_configuration_help() {
        return get_string('nativemariadbhelp', 'install');
    }

    
    public function get_dbvendor() {
        return 'mariadb';
    }

    
    protected function get_dbtype() {
        return 'mariadb';
    }

    
    public function get_server_info() {
        $version = $this->mysqli->server_info;
        $matches = null;
        if (preg_match('/^5\.5\.5-(10\..+)-MariaDB/i', $version, $matches)) {
                        $version = $matches[1];
        }
        return array('description'=>$this->mysqli->server_info, 'version'=>$version);
    }

    
    protected function transactions_supported() {
        if ($this->external) {
            return parent::transactions_supported();
        }
        return true;
    }
}
