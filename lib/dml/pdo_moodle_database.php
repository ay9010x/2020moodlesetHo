<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/moodle_database.php');
require_once(__DIR__.'/pdo_moodle_recordset.php');


abstract class pdo_moodle_database extends moodle_database {

    protected $pdb;
    protected $lastError = null;

    
    public function __construct($external=false) {
        parent::__construct($external);
    }

    
    public function connect($dbhost, $dbuser, $dbpass, $dbname, $prefix, array $dboptions=null) {
        $driverstatus = $this->driver_installed();

        if ($driverstatus !== true) {
            throw new dml_exception('dbdriverproblem', $driverstatus);
        }

        $this->store_settings($dbhost, $dbuser, $dbpass, $dbname, $prefix, $dboptions);

        try{
            $this->pdb = new PDO($this->get_dsn(), $this->dbuser, $this->dbpass, $this->get_pdooptions());
                        $this->pdb->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
            $this->pdb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->configure_dbconnection();
            return true;
        } catch (PDOException $ex) {
            throw new dml_connection_exception($ex->getMessage());
            return false;
        }
    }

    
    abstract protected function get_dsn();

    
    protected function get_pdooptions() {
        return array(PDO::ATTR_PERSISTENT => !empty($this->dboptions['dbpersist']));
    }

    protected function configure_dbconnection() {
            }

    
    protected function get_dblibrary() {
        return 'pdo';
    }

    
    public function get_name() {
        return get_string('pdo'.$this->get_dbtype(), 'install');
    }

    
    public function get_configuration_help() {
        return get_string('pdo'.$this->get_dbtype().'help', 'install');
    }

    
    public function get_server_info() {
        $result = array();
        try {
            $result['description'] = $this->pdb->getAttribute(PDO::ATTR_SERVER_INFO);
        } catch(PDOException $ex) {}
        try {
            $result['version'] = $this->pdb->getAttribute(PDO::ATTR_SERVER_VERSION);
        } catch(PDOException $ex) {}
        return $result;
    }

    
    protected function allowed_param_types() {
        return SQL_PARAMS_QM | SQL_PARAMS_NAMED;
    }

    
    public function get_last_error() {
        return $this->lastError;
    }

    
    protected function debug_query($sql, $params = null) {
        echo '<hr /> (', $this->get_dbtype(), '): ',  htmlentities($sql, ENT_QUOTES, 'UTF-8');
        if($params) {
            echo ' (parameters ';
            print_r($params);
            echo ')';
        }
        echo '<hr />';
    }

    
    public function change_database_structure($sql, $tablenames = null) {
        $this->get_manager();         $sqls = (array)$sql;

        try {
            foreach ($sqls as $sql) {
                $result = true;
                $this->query_start($sql, null, SQL_QUERY_STRUCTURE);

                try {
                    $this->pdb->exec($sql);
                } catch (PDOException $ex) {
                    $this->lastError = $ex->getMessage();
                    $result = false;
                }
                $this->query_end($result);
            }
        } catch (ddl_change_structure_exception $e) {
            $this->reset_caches($tablenames);
            throw $e;
        }

        $this->reset_caches($tablenames);
        return true;
    }

    public function delete_records_select($table, $select, array $params=null) {
        $sql = "DELETE FROM {{$table}}";
        if ($select) {
            $sql .= " WHERE $select";
        }
        return $this->execute($sql, $params);
    }

    
    protected function create_recordset($sth) {
        return new pdo_moodle_recordset($sth);
    }

    
    public function execute($sql, array $params=null) {
        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);

        $result = true;
        $this->query_start($sql, $params, SQL_QUERY_UPDATE);

        try {
            $sth = $this->pdb->prepare($sql);
            $sth->execute($params);
        } catch (PDOException $ex) {
            $this->lastError = $ex->getMessage();
            $result = false;
        }

        $this->query_end($result);
        return $result;
    }

    
    public function get_recordset_sql($sql, array $params=null, $limitfrom=0, $limitnum=0) {

        $result = true;

        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);
        $sql = $this->get_limit_clauses($sql, $limitfrom, $limitnum);
        $this->query_start($sql, $params, SQL_QUERY_SELECT);

        try {
            $sth = $this->pdb->prepare($sql);
            $sth->execute($params);
            $result = $this->create_recordset($sth);
        } catch (PDOException $ex) {
            $this->lastError = $ex->getMessage();
            $result = false;
        }

        $this->query_end($result);
        return $result;
    }

    
    public function get_fieldset_sql($sql, array $params=null) {
        $rs = $this->get_recordset_sql($sql, $params);
        if (!$rs->valid()) {
            $rs->close();             return false;
        }
        $result = array();
        foreach($rs as $value) {
            $result[] = reset($value);
        }
        $rs->close();
        return $result;
    }

    
    public function get_records_sql($sql, array $params=null, $limitfrom=0, $limitnum=0) {
        global $CFG;

        $rs = $this->get_recordset_sql($sql, $params, $limitfrom, $limitnum);
        if (!$rs->valid()) {
            $rs->close();             return false;
        }
        $objects = array();
        foreach($rs as $value) {
            $key = reset($value);
            if ($CFG->debugdeveloper && array_key_exists($key, $objects)) {
                debugging("Did you remember to make the first column something unique in your call to get_records? Duplicate value '$key' found in column first column of '$sql'.", DEBUG_DEVELOPER);
            }
            $objects[$key] = (object)$value;
        }
        $rs->close();
        return $objects;
    }

    
    public function insert_record_raw($table, $params, $returnid=true, $bulk=false, $customsequence=false) {
        if (!is_array($params)) {
            $params = (array)$params;
        }

        if ($customsequence) {
            if (!isset($params['id'])) {
                throw new coding_exception('moodle_database::insert_record_raw() id field must be specified if custom sequences used.');
            }
            $returnid = false;
        } else {
            unset($params['id']);
        }

        if (empty($params)) {
            throw new coding_exception('moodle_database::insert_record_raw() no fields found.');
        }

        $fields = implode(',', array_keys($params));
        $qms    = array_fill(0, count($params), '?');
        $qms    = implode(',', $qms);

        $sql = "INSERT INTO {{$table}} ($fields) VALUES($qms)";
        if (!$this->execute($sql, $params)) {
            return false;
        }
        if (!$returnid) {
            return true;
        }
        if ($id = $this->pdb->lastInsertId()) {
            return (int)$id;
        }
        return false;
    }

    
    public function insert_record($table, $dataobject, $returnid=true, $bulk=false) {
        $dataobject = (array)$dataobject;

        $columns = $this->get_columns($table);
        if (empty($columns)) {
            throw new dml_exception('ddltablenotexist', $table);
        }

        $cleaned = array();

        foreach ($dataobject as $field=>$value) {
            if ($field === 'id') {
                continue;
            }
            if (!isset($columns[$field])) {
                continue;
            }
            $column = $columns[$field];
            if (is_bool($value)) {
                $value = (int)$value;             }
            $cleaned[$field] = $value;
        }

        if (empty($cleaned)) {
            return false;
        }

        return $this->insert_record_raw($table, $cleaned, $returnid, $bulk);
    }

    
    public function update_record_raw($table, $params, $bulk=false) {
        $params = (array)$params;

        if (!isset($params['id'])) {
            throw new coding_exception('moodle_database::update_record_raw() id field must be specified.');
        }
        $id = $params['id'];
        unset($params['id']);

        if (empty($params)) {
            throw new coding_exception('moodle_database::update_record_raw() no fields found.');
        }

        $sets = array();
        foreach ($params as $field=>$value) {
            $sets[] = "$field = ?";
        }

        $params[] = $id; 
        $sets = implode(',', $sets);
        $sql = "UPDATE {{$table}} SET $sets WHERE id=?";
        return $this->execute($sql, $params);
    }

    
    public function update_record($table, $dataobject, $bulk=false) {
        $dataobject = (array)$dataobject;

        $columns = $this->get_columns($table);
        $cleaned = array();

        foreach ($dataobject as $field=>$value) {
            if (!isset($columns[$field])) {
                continue;
            }
            if (is_bool($value)) {
                $value = (int)$value;             }
            $cleaned[$field] = $value;
        }

        return $this->update_record_raw($table, $cleaned, $bulk);
    }

    
    public function set_field_select($table, $newfield, $newvalue, $select, array $params=null) {
        if ($select) {
            $select = "WHERE $select";
        }
        if (is_null($params)) {
            $params = array();
        }
        list($select, $params, $type) = $this->fix_sql_params($select, $params);

        if (is_bool($newvalue)) {
            $newvalue = (int)$newvalue;         }
        if (is_null($newvalue)) {
            $newfield = "$newfield = NULL";
        } else {
                                    switch($type) {
            case SQL_PARAMS_NAMED:
                $newfield = "$newfield = :newvalueforupdate";
                $params['newvalueforupdate'] = $newvalue;
                break;
            case SQL_PARAMS_QM:
                $newfield = "$newfield = ?";
                array_unshift($params, $newvalue);
                break;
            default:
                $this->lastError = __FILE__ . ' LINE: ' . __LINE__ . '.';
                print_error(unknowparamtype, 'error', '', $this->lastError);
            }
        }
        $sql = "UPDATE {{$table}} SET $newfield $select";
        return $this->execute($sql, $params);
    }

    public function sql_concat() {
        print_error('TODO');
    }

    public function sql_concat_join($separator="' '", $elements=array()) {
        print_error('TODO');
    }

    protected function begin_transaction() {
        $this->query_start('', NULL, SQL_QUERY_AUX);
        try {
            $this->pdb->beginTransaction();
        } catch(PDOException $ex) {
            $this->lastError = $ex->getMessage();
        }
        $this->query_end($result);
    }

    protected function commit_transaction() {
        $this->query_start('', NULL, SQL_QUERY_AUX);

        try {
            $this->pdb->commit();
        } catch(PDOException $ex) {
            $this->lastError = $ex->getMessage();
        }
        $this->query_end($result);
    }

    protected function rollback_transaction() {
        $this->query_start('', NULL, SQL_QUERY_AUX);

        try {
            $this->pdb->rollBack();
        } catch(PDOException $ex) {
            $this->lastError = $ex->getMessage();
        }
        $this->query_end($result);
    }

    
    public function import_record($table, $dataobject) {
        $dataobject = (object)$dataobject;

        $columns = $this->get_columns($table);
        $cleaned = array();
        foreach ($dataobject as $field=>$value) {
            if (!isset($columns[$field])) {
                continue;
            }
            $cleaned[$field] = $value;
        }

        return $this->insert_record_raw($table, $cleaned, false, true, true);
    }

    
    protected function query_start($sql, array $params=null, $type, $extrainfo=null) {
        $this->lastError = null;
        parent::query_start($sql, $params, $type, $extrainfo);
    }
}
