<?php




abstract class testing_util {

    
    private static $dataroot = null;

    
    protected static $generator = null;

    
    protected static $versionhash = null;

    
    protected static $tabledata = null;

    
    protected static $tablestructure = null;

    
    private static $tablesequences = array();

    
    public static $tableupdated = array();

    
    protected static $sequencenames = null;

    
    private static $originaldatafilesjson = 'originaldatafiles.json';

    
    private static $originaldatafilesjsonadded = false;

    
    protected static $sequencenextstartingid = null;

    
    public static function get_originaldatafilesjson() {
        return self::$originaldatafilesjson;
    }

    
    public static function get_dataroot() {
        global $CFG;

                if (empty(self::$dataroot)) {
            self::$dataroot = $CFG->dataroot;
        }

        return self::$dataroot;
    }

    
    public static function set_dataroot($dataroot) {
        self::$dataroot = $dataroot;
    }

    
    protected static final function get_framework() {
        $classname = get_called_class();
        return substr($classname, 0, strpos($classname, '_'));
    }

    
    public static function get_data_generator() {
        if (is_null(self::$generator)) {
            require_once(__DIR__.'/../generator/lib.php');
            self::$generator = new testing_data_generator();
        }
        return self::$generator;
    }

    
    public static function is_test_site() {
        global $DB, $CFG;

        $framework = self::get_framework();

        if (!file_exists(self::get_dataroot() . '/' . $framework . 'testdir.txt')) {
                                    return false;
        }

        $tables = $DB->get_tables(false);
        if ($tables) {
            if (!$DB->get_manager()->table_exists('config')) {
                return false;
            }
            if (!get_config('core', $framework . 'test')) {
                return false;
            }
        }

        return true;
    }

    
    public static function is_test_data_updated() {
        global $DB;

        $framework = self::get_framework();

        $datarootpath = self::get_dataroot() . '/' . $framework;
        if (!file_exists($datarootpath . '/tabledata.ser') or !file_exists($datarootpath . '/tablestructure.ser')) {
            return false;
        }

        if (!file_exists($datarootpath . '/versionshash.txt')) {
            return false;
        }

        $hash = core_component::get_all_versions_hash();
        $oldhash = file_get_contents($datarootpath . '/versionshash.txt');

        if ($hash !== $oldhash) {
            return false;
        }

                $dbhash = $DB->get_field('config', 'value', array('name' => $framework . 'test'));
        if ($hash !== $dbhash) {
            return false;
        }

        return true;
    }

    
    protected static function store_database_state() {
        global $DB, $CFG;

        $framework = self::get_framework();

                $data = array();
        $structure = array();
        $tables = $DB->get_tables();
        foreach ($tables as $table) {
            $columns = $DB->get_columns($table);
            $structure[$table] = $columns;
            if (isset($columns['id']) and $columns['id']->auto_increment) {
                $data[$table] = $DB->get_records($table, array(), 'id ASC');
            } else {
                                $data[$table] = $DB->get_records($table, array());
            }
        }
        $data = serialize($data);
        $datafile = self::get_dataroot() . '/' . $framework . '/tabledata.ser';
        file_put_contents($datafile, $data);
        testing_fix_file_permissions($datafile);

        $structure = serialize($structure);
        $structurefile = self::get_dataroot() . '/' . $framework . '/tablestructure.ser';
        file_put_contents($structurefile, $structure);
        testing_fix_file_permissions($structurefile);
    }

    
    protected static function store_versions_hash() {
        global $CFG;

        $framework = self::get_framework();
        $hash = core_component::get_all_versions_hash();

                set_config($framework . 'test', $hash);

                $hashfile = self::get_dataroot() . '/' . $framework . '/versionshash.txt';
        file_put_contents($hashfile, $hash);
        testing_fix_file_permissions($hashfile);
    }

    
    protected static function get_tabledata() {
        if (!isset(self::$tabledata)) {
            $framework = self::get_framework();

            $datafile = self::get_dataroot() . '/' . $framework . '/tabledata.ser';
            if (!file_exists($datafile)) {
                                return array();
            }

            $data = file_get_contents($datafile);
            self::$tabledata = unserialize($data);
        }

        if (!is_array(self::$tabledata)) {
            testing_error(1, 'Can not read dataroot/' . $framework . '/tabledata.ser or invalid format, reinitialize test database.');
        }

        return self::$tabledata;
    }

    
    public static function get_tablestructure() {
        if (!isset(self::$tablestructure)) {
            $framework = self::get_framework();

            $structurefile = self::get_dataroot() . '/' . $framework . '/tablestructure.ser';
            if (!file_exists($structurefile)) {
                                return array();
            }

            $data = file_get_contents($structurefile);
            self::$tablestructure = unserialize($data);
        }

        if (!is_array(self::$tablestructure)) {
            testing_error(1, 'Can not read dataroot/' . $framework . '/tablestructure.ser or invalid format, reinitialize test database.');
        }

        return self::$tablestructure;
    }

    
    public static function get_sequencenames() {
        global $DB;

        if (isset(self::$sequencenames)) {
            return self::$sequencenames;
        }

        if (!$structure = self::get_tablestructure()) {
            return array();
        }

        self::$sequencenames = array();
        foreach ($structure as $table => $ignored) {
            $name = $DB->get_manager()->generator->getSequenceFromDB(new xmldb_table($table));
            if ($name !== false) {
                self::$sequencenames[$table] = $name;
            }
        }

        return self::$sequencenames;
    }

    
    protected static function guess_unmodified_empty_tables() {
        global $DB;

        $dbfamily = $DB->get_dbfamily();

        if ($dbfamily === 'mysql') {
            $empties = array();
            $prefix = $DB->get_prefix();
            $rs = $DB->get_recordset_sql("SHOW TABLE STATUS LIKE ?", array($prefix.'%'));
            foreach ($rs as $info) {
                $table = strtolower($info->name);
                if (strpos($table, $prefix) !== 0) {
                                        continue;
                }

                if (!is_null($info->auto_increment) && $info->rows == 0 && ($info->auto_increment == 1)) {
                    $table = preg_replace('/^'.preg_quote($prefix, '/').'/', '', $table);
                    $empties[$table] = $table;
                }
            }
            $rs->close();
            return $empties;

        } else if ($dbfamily === 'mssql') {
            $empties = array();
            $prefix = $DB->get_prefix();
            $sql = "SELECT t.name
                      FROM sys.identity_columns i
                      JOIN sys.tables t ON t.object_id = i.object_id
                     WHERE t.name LIKE ?
                       AND i.name = 'id'
                       AND i.last_value IS NULL";
            $rs = $DB->get_recordset_sql($sql, array($prefix.'%'));
            foreach ($rs as $info) {
                $table = strtolower($info->name);
                if (strpos($table, $prefix) !== 0) {
                                        continue;
                }
                $table = preg_replace('/^'.preg_quote($prefix, '/').'/', '', $table);
                $empties[$table] = $table;
            }
            $rs->close();
            return $empties;

        } else if ($dbfamily === 'oracle') {
            $sequences = self::get_sequencenames();
            $sequences = array_map('strtoupper', $sequences);
            $lookup = array_flip($sequences);
            $empties = array();
            list($seqs, $params) = $DB->get_in_or_equal($sequences);
            $sql = "SELECT sequence_name FROM user_sequences WHERE last_number = 1 AND sequence_name $seqs";
            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $seq) {
                $table = $lookup[$seq->sequence_name];
                $empties[$table] = $table;
            }
            $rs->close();
            return $empties;

        } else {
            return array();
        }
    }

    
    private static function get_next_sequence_starting_value($records, $table) {
        if (isset(self::$tablesequences[$table])) {
            return self::$tablesequences[$table];
        }

        $id = self::$sequencenextstartingid;

                        if (!empty($records)) {
            $lastrecord = end($records);
            $id = max($id, $lastrecord->id + 1);
        }

        self::$sequencenextstartingid = $id + 1000;

        self::$tablesequences[$table] = $id;

        return $id;
    }

    
    public static function reset_all_database_sequences(array $empties = null) {
        global $DB;

        if (!$data = self::get_tabledata()) {
                        return;
        }
        if (!$structure = self::get_tablestructure()) {
                        return;
        }

        $updatedtables = self::$tableupdated;

                                                if (defined('PHPUNIT_SEQUENCE_START') and PHPUNIT_SEQUENCE_START) {
            self::$sequencenextstartingid = PHPUNIT_SEQUENCE_START;
        } else {
            self::$sequencenextstartingid = 100000;
        }

        $dbfamily = $DB->get_dbfamily();
        if ($dbfamily === 'postgres') {
            $queries = array();
            $prefix = $DB->get_prefix();
            foreach ($data as $table => $records) {
                                if (!isset($updatedtables[$table])) {
                    continue;
                }
                if (isset($structure[$table]['id']) and $structure[$table]['id']->auto_increment) {
                    $nextid = self::get_next_sequence_starting_value($records, $table);
                    $queries[] = "ALTER SEQUENCE {$prefix}{$table}_id_seq RESTART WITH $nextid";
                }
            }
            if ($queries) {
                $DB->change_database_structure(implode(';', $queries));
            }

        } else if ($dbfamily === 'mysql') {
            $queries = array();
            $sequences = array();
            $prefix = $DB->get_prefix();
            $rs = $DB->get_recordset_sql("SHOW TABLE STATUS LIKE ?", array($prefix.'%'));
            foreach ($rs as $info) {
                $table = strtolower($info->name);
                if (strpos($table, $prefix) !== 0) {
                                        continue;
                }
                if (!is_null($info->auto_increment)) {
                    $table = preg_replace('/^'.preg_quote($prefix, '/').'/', '', $table);
                    $sequences[$table] = $info->auto_increment;
                }
            }
            $rs->close();
            $prefix = $DB->get_prefix();
            foreach ($data as $table => $records) {
                                if (!isset($updatedtables[$table])) {
                    continue;
                }
                if (isset($structure[$table]['id']) and $structure[$table]['id']->auto_increment) {
                    if (isset($sequences[$table])) {
                        $nextid = self::get_next_sequence_starting_value($records, $table);
                        if ($sequences[$table] != $nextid) {
                            $queries[] = "ALTER TABLE {$prefix}{$table} AUTO_INCREMENT = $nextid";
                        }
                    } else {
                                                $DB->get_manager()->reset_sequence($table);
                    }
                }
            }
            if ($queries) {
                $DB->change_database_structure(implode(';', $queries));
            }

        } else if ($dbfamily === 'oracle') {
            $sequences = self::get_sequencenames();
            $sequences = array_map('strtoupper', $sequences);
            $lookup = array_flip($sequences);

            $current = array();
            list($seqs, $params) = $DB->get_in_or_equal($sequences);
            $sql = "SELECT sequence_name, last_number FROM user_sequences WHERE sequence_name $seqs";
            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $seq) {
                $table = $lookup[$seq->sequence_name];
                $current[$table] = $seq->last_number;
            }
            $rs->close();

            foreach ($data as $table => $records) {
                                if (!isset($updatedtables[$table])) {
                    continue;
                }
                if (isset($structure[$table]['id']) and $structure[$table]['id']->auto_increment) {
                    $lastrecord = end($records);
                    if ($lastrecord) {
                        $nextid = $lastrecord->id + 1;
                    } else {
                        $nextid = 1;
                    }
                    if (!isset($current[$table])) {
                        $DB->get_manager()->reset_sequence($table);
                    } else if ($nextid == $current[$table]) {
                        continue;
                    }
                                        $seqname = $sequences[$table];
                    $cachesize = $DB->get_manager()->generator->sequence_cache_size;
                    $DB->change_database_structure("DROP SEQUENCE $seqname");
                    $DB->change_database_structure("CREATE SEQUENCE $seqname START WITH $nextid INCREMENT BY 1 NOMAXVALUE CACHE $cachesize");
                }
            }

        } else {
                                    if (is_null($empties) and (empty($updatedtables))) {
                $empties = self::guess_unmodified_empty_tables();
            }
            foreach ($data as $table => $records) {
                                if (isset($empties[$table]) or (!isset($updatedtables[$table]))) {
                    continue;
                }
                if (isset($structure[$table]['id']) and $structure[$table]['id']->auto_increment) {
                    $DB->get_manager()->reset_sequence($table);
                }
            }
        }
    }

    
    public static function reset_database() {
        global $DB;

        $tables = $DB->get_tables(false);
        if (!$tables or empty($tables['config'])) {
                        return false;
        }

        if (!$data = self::get_tabledata()) {
                        return false;
        }
        if (!$structure = self::get_tablestructure()) {
                        return false;
        }

        $empties = array();
                $updatedtables = self::$tableupdated;

                if (empty(self::$tablesequences) && (($DB->get_dbfamily() != 'mysql') && ($DB->get_dbfamily() != 'postgres'))) {
                        $empties = self::guess_unmodified_empty_tables();
        }

                if (defined('BEHAT_SITE_RUNNING')) {
                        $tablesupdatedfile = self::get_tables_updated_by_scenario_list_path();
            if ($tablesupdated = @json_decode(file_get_contents($tablesupdatedfile), true)) {
                self::$tableupdated = array_merge(self::$tableupdated, $tablesupdated);
                unlink($tablesupdatedfile);
            }
            $updatedtables = self::$tableupdated;
        }

        $borkedmysql = false;
        if ($DB->get_dbfamily() === 'mysql') {
            $version = $DB->get_server_info();
            if (version_compare($version['version'], '5.6.0') == 1 and version_compare($version['version'], '5.6.16') == -1) {
                                                                                                                                                $borkedmysql = true;

            } else if (version_compare($version['version'], '10.0.0') == 1) {
                                                $borkedmysql = true;
            }
        }

        if ($borkedmysql) {
            $mysqlsequences = array();
            $prefix = $DB->get_prefix();
            $rs = $DB->get_recordset_sql("SHOW TABLE STATUS LIKE ?", array($prefix.'%'));
            foreach ($rs as $info) {
                $table = strtolower($info->name);
                if (strpos($table, $prefix) !== 0) {
                                        continue;
                }
                if (!is_null($info->auto_increment)) {
                    $table = preg_replace('/^'.preg_quote($prefix, '/').'/', '', $table);
                    $mysqlsequences[$table] = $info->auto_increment;
                }
            }
        }

        foreach ($data as $table => $records) {
                                    if (!empty($updatedtables) && !isset($updatedtables[$table])) {
                continue;
            }

            if ($borkedmysql) {
                if (empty($records)) {
                    if (!isset($empties[$table])) {
                                                $DB->delete_records($table, null);
                    }
                    continue;
                }

                if (isset($structure[$table]['id']) and $structure[$table]['id']->auto_increment) {
                    $current = $DB->get_records($table, array(), 'id ASC');
                    if ($current == $records) {
                        if (isset($mysqlsequences[$table]) and $mysqlsequences[$table] == $structure[$table]['id']->auto_increment) {
                            continue;
                        }
                    }
                }

                                $DB->delete_records($table, null);
                foreach ($records as $record) {
                    $DB->import_record($table, $record, false, true);
                }
                continue;
            }

            if (empty($records)) {
                if (!isset($empties[$table])) {
                                        $DB->delete_records($table, array());
                }
                continue;
            }

            if (isset($structure[$table]['id']) and $structure[$table]['id']->auto_increment) {
                $currentrecords = $DB->get_records($table, array(), 'id ASC');
                $changed = false;
                foreach ($records as $id => $record) {
                    if (!isset($currentrecords[$id])) {
                        $changed = true;
                        break;
                    }
                    if ((array)$record != (array)$currentrecords[$id]) {
                        $changed = true;
                        break;
                    }
                    unset($currentrecords[$id]);
                }
                if (!$changed) {
                    if ($currentrecords) {
                        $lastrecord = end($records);
                        $DB->delete_records_select($table, "id > ?", array($lastrecord->id));
                        continue;
                    } else {
                        continue;
                    }
                }
            }

            $DB->delete_records($table, array());
            foreach ($records as $record) {
                $DB->import_record($table, $record, false, true);
            }
        }

                self::reset_all_database_sequences($empties);

                foreach ($tables as $table) {
            if (!isset($data[$table])) {
                $DB->get_manager()->drop_table(new xmldb_table($table));
            }
        }

        self::reset_updated_table_list();

        return true;
    }

    
    public static function reset_dataroot() {
        global $CFG;

        $childclassname = self::get_framework() . '_util';

                self::skip_original_data_files($childclassname);

                clearstatcache();

                $handle = opendir(self::get_dataroot());
        while (false !== ($item = readdir($handle))) {
            if (in_array($item, $childclassname::$datarootskiponreset)) {
                continue;
            }
            if (is_dir(self::get_dataroot()."/$item")) {
                remove_dir(self::get_dataroot()."/$item", false);
            } else {
                unlink(self::get_dataroot()."/$item");
            }
        }
        closedir($handle);

                if (file_exists(self::get_dataroot() . '/filedir')) {
            $handle = opendir(self::get_dataroot() . '/filedir');
            while (false !== ($item = readdir($handle))) {
                if (in_array('filedir' . DIRECTORY_SEPARATOR . $item, $childclassname::$datarootskiponreset)) {
                    continue;
                }
                if (is_dir(self::get_dataroot()."/filedir/$item")) {
                    remove_dir(self::get_dataroot()."/filedir/$item", false);
                } else {
                    unlink(self::get_dataroot()."/filedir/$item");
                }
            }
            closedir($handle);
        }

        make_temp_directory('');
        make_cache_directory('');
        make_localcache_directory('');
                cache_factory::reset();
                                cache_helper::purge_all();
    }

    
    public static function get_site_info() {
        global $CFG;

        $output = '';

                $env = self::get_environment();

        $output .= "Moodle ".$env['moodleversion'];
        if ($hash = self::get_git_hash()) {
            $output .= ", $hash";
        }
        $output .= "\n";

                require_once($CFG->libdir.'/environmentlib.php');
        $output .= "Php: ". normalize_version($env['phpversion']);

                $output .= ", " . $env['dbtype'] . ": " . $env['dbversion'];

                $output .= ", OS: " . $env['os'] . "\n";

        return $output;
    }

    
    public static function get_git_hash() {
        global $CFG;

        
        if (!file_exists("$CFG->dirroot/.git/HEAD")) {
            return null;
        }

        $headcontent = file_get_contents("$CFG->dirroot/.git/HEAD");
        if ($headcontent === false) {
            return null;
        }

        $headcontent = trim($headcontent);

                if (strlen($headcontent) === 40) {
            return $headcontent;
        }

        if (strpos($headcontent, 'ref: ') !== 0) {
            return null;
        }

        $ref = substr($headcontent, 5);

        if (!file_exists("$CFG->dirroot/.git/$ref")) {
            return null;
        }

        $hash = file_get_contents("$CFG->dirroot/.git/$ref");

        if ($hash === false) {
            return null;
        }

        $hash = trim($hash);

        if (strlen($hash) != 40) {
            return null;
        }

        return $hash;
    }

    
    public static function set_table_modified_by_sql($sql) {
        global $DB;

        $prefix = $DB->get_prefix();

        preg_match('/( ' . $prefix . '\w*)(.*)/', $sql, $matches);
                if (!empty($matches[1])) {
            $table = trim($matches[1]);
            $table = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $table);
            self::$tableupdated[$table] = true;

            if (defined('BEHAT_SITE_RUNNING')) {
                $tablesupdatedfile = self::get_tables_updated_by_scenario_list_path();
                if ($tablesupdated = @json_decode(file_get_contents($tablesupdatedfile), true)) {
                    $tablesupdated[$table] = true;
                } else {
                    $tablesupdated[$table] = true;
                }
                @file_put_contents($tablesupdatedfile, json_encode($tablesupdated, JSON_PRETTY_PRINT));
            }
        }
    }

    
    public static function reset_updated_table_list() {
        self::$tableupdated = array();
    }

    
    public static function clean_tables_updated_by_scenario_list() {
        $tablesupdatedfile = self::get_tables_updated_by_scenario_list_path();
        if (file_exists($tablesupdatedfile)) {
            unlink($tablesupdatedfile);
        }

                self::reset_updated_table_list();
    }

    
    protected final static function get_tables_updated_by_scenario_list_path() {
        return self::get_dataroot() . '/tablesupdatedbyscenario.json';
    }

    
    protected static function drop_database($displayprogress = false) {
        global $DB;

        $tables = $DB->get_tables(false);
        if (isset($tables['config'])) {
                        unset($tables['config']);
            $tables['config'] = 'config';
        }

        if ($displayprogress) {
            echo "Dropping tables:\n";
        }
        $dotsonline = 0;
        foreach ($tables as $tablename) {
            $table = new xmldb_table($tablename);
            $DB->get_manager()->drop_table($table);

            if ($dotsonline == 60) {
                if ($displayprogress) {
                    echo "\n";
                }
                $dotsonline = 0;
            }
            if ($displayprogress) {
                echo '.';
            }
            $dotsonline += 1;
        }
        if ($displayprogress) {
            echo "\n";
        }
    }

    
    protected static function drop_dataroot() {
        global $CFG;

        $framework = self::get_framework();
        $childclassname = $framework . '_util';

        $files = scandir(self::get_dataroot() . '/'  . $framework);
        foreach ($files as $file) {
            if (in_array($file, $childclassname::$datarootskipondrop)) {
                continue;
            }
            $path = self::get_dataroot() . '/' . $framework . '/' . $file;
            if (is_dir($path)) {
                remove_dir($path, false);
            } else {
                unlink($path);
            }
        }

        $jsonfilepath = self::get_dataroot() . '/' . self::$originaldatafilesjson;
        if (file_exists($jsonfilepath)) {
                        unlink($jsonfilepath);
                        remove_dir(self::get_dataroot() . '/filedir', false);
        }
    }

    
    protected static function skip_original_data_files($utilclassname) {
        $jsonfilepath = self::get_dataroot() . '/' . self::$originaldatafilesjson;
        if (file_exists($jsonfilepath)) {

            $listfiles = file_get_contents($jsonfilepath);

                        if (!empty($listfiles) && !self::$originaldatafilesjsonadded) {
                $originaldatarootfiles = json_decode($listfiles);
                                $originaldatarootfiles[] = self::$originaldatafilesjson;
                $utilclassname::$datarootskiponreset = array_merge($utilclassname::$datarootskiponreset,
                    $originaldatarootfiles);
                self::$originaldatafilesjsonadded = true;
            }
        }
    }

    
    protected static function save_original_data_files() {
        global $CFG;

        $jsonfilepath = self::get_dataroot() . '/' . self::$originaldatafilesjson;

                if (!file_exists($jsonfilepath)) {

            $listfiles = array();
            $currentdir = 'filedir' . DIRECTORY_SEPARATOR . '.';
            $parentdir = 'filedir' . DIRECTORY_SEPARATOR . '..';
            $listfiles[$currentdir] = $currentdir;
            $listfiles[$parentdir] = $parentdir;

            $filedir = self::get_dataroot() . '/filedir';
            if (file_exists($filedir)) {
                $directory = new RecursiveDirectoryIterator($filedir);
                foreach (new RecursiveIteratorIterator($directory) as $file) {
                    if ($file->isDir()) {
                        $key = substr($file->getPath(), strlen(self::get_dataroot() . '/'));
                    } else {
                        $key = substr($file->getPathName(), strlen(self::get_dataroot() . '/'));
                    }
                    $listfiles[$key] = $key;
                }
            }

                        $fp = fopen($jsonfilepath, 'w');
            fwrite($fp, json_encode(array_values($listfiles)));
            fclose($fp);
        }
    }

    
    public static function get_environment() {
        global $CFG, $DB;

        $env = array();

                $release = null;
        require("$CFG->dirroot/version.php");
        $env['moodleversion'] = $release;

                $phpversion = phpversion();
        $env['phpversion'] = $phpversion;

                $dbtype = $CFG->dbtype;
        $dbinfo = $DB->get_server_info();
        $dbversion = $dbinfo['version'];
        $env['dbtype'] = $dbtype;
        $env['dbversion'] = $dbversion;

                $osdetails = php_uname('s') . " " . php_uname('r') . " " . php_uname('m');
        $env['os'] = $osdetails;

        return $env;
    }
}
