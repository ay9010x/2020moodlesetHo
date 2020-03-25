<?php



defined('MOODLE_INTERNAL') || die();


abstract class grade_object {
    
    public $table;

    
    public $required_fields = array('id', 'timecreated', 'timemodified', 'hidden');

    
    public $optional_fields = array();

    
    public $id;

    
    public $timecreated;

    
    public $timemodified;

    
    var $hidden = 0;

    
    public function __construct($params=NULL, $fetch=true) {
        if (!empty($params) and (is_array($params) or is_object($params))) {
            if ($fetch) {
                if ($data = $this->fetch($params)) {
                    grade_object::set_properties($this, $data);
                } else {
                    grade_object::set_properties($this, $this->optional_fields);                    grade_object::set_properties($this, $params);
                }

            } else {
                grade_object::set_properties($this, $params);
            }

        } else {
            grade_object::set_properties($this, $this->optional_fields);        }
    }

    
    public function load_optional_fields() {
        global $DB;
        foreach ($this->optional_fields as $field=>$default) {
            if (property_exists($this, $field)) {
                continue;
            }
            if (empty($this->id)) {
                $this->$field = $default;
            } else {
                $this->$field = $DB->get_field($this->table, $field, array('id', $this->id));
            }
        }
    }

    
    public static function fetch($params) {
        throw new coding_exception('fetch() method needs to be overridden in each subclass of grade_object');
    }

    
    public static function fetch_all($params) {
        throw new coding_exception('fetch_all() method needs to be overridden in each subclass of grade_object');
    }

    
    protected static function fetch_helper($table, $classname, $params) {
        if ($instances = grade_object::fetch_all_helper($table, $classname, $params)) {
            if (count($instances) > 1) {
                                print_error('morethanonerecordinfetch','debug');
            }
            return reset($instances);
        } else {
            return false;
        }
    }

    
    public static function fetch_all_helper($table, $classname, $params) {
        global $DB; 
        $instance = new $classname();

        $classvars = (array)$instance;
        $params    = (array)$params;

        $wheresql = array();
        $newparams = array();

        $columns = $DB->get_columns($table); 
        foreach ($params as $var=>$value) {
            if (!in_array($var, $instance->required_fields) and !array_key_exists($var, $instance->optional_fields)) {
                continue;
            }
            if (!array_key_exists($var, $columns)) {
                continue;
            }
            if (is_null($value)) {
                $wheresql[] = " $var IS NULL ";
            } else {
                if ($columns[$var]->meta_type === 'X') {
                                        $wheresql[] = ' ' . $DB->sql_compare_text($var) . ' = ' . $DB->sql_compare_text('?') . ' ';
                } else {
                                        $wheresql[] = " $var = ? ";
                }
                $newparams[] = $value;
            }
        }

        if (empty($wheresql)) {
            $wheresql = '';
        } else {
            $wheresql = implode("AND", $wheresql);
        }

        global $DB;
        $rs = $DB->get_recordset_select($table, $wheresql, $newparams);
                if (!$rs->valid()) {
            $rs->close();
            return false;
        }

        $result = array();
        foreach($rs as $data) {
            $instance = new $classname();
            grade_object::set_properties($instance, $data);
            $result[$instance->id] = $instance;
        }
        $rs->close();

        return $result;
    }

    
    public function update($source=null) {
        global $USER, $CFG, $DB;

        if (empty($this->id)) {
            debugging('Can not update grade object, no id!');
            return false;
        }

        $data = $this->get_record_data();

        $DB->update_record($this->table, $data);

        if (empty($CFG->disablegradehistory)) {
            unset($data->timecreated);
            $data->action       = GRADE_HISTORY_UPDATE;
            $data->oldid        = $this->id;
            $data->source       = $source;
            $data->timemodified = time();
            $data->loggeduser   = $USER->id;
            $DB->insert_record($this->table.'_history', $data);
        }

        $this->notify_changed(false);
        return true;
    }

    
    public function delete($source=null) {
        global $USER, $CFG, $DB;

        if (empty($this->id)) {
            debugging('Can not delete grade object, no id!');
            return false;
        }

        $data = $this->get_record_data();

        if ($DB->delete_records($this->table, array('id'=>$this->id))) {
            if (empty($CFG->disablegradehistory)) {
                unset($data->id);
                unset($data->timecreated);
                $data->action       = GRADE_HISTORY_DELETE;
                $data->oldid        = $this->id;
                $data->source       = $source;
                $data->timemodified = time();
                $data->loggeduser   = $USER->id;
                $DB->insert_record($this->table.'_history', $data);
            }
            $this->notify_changed(true);
            return true;

        } else {
            return false;
        }
    }

    
    public function get_record_data() {
        $data = new stdClass();

        foreach ($this as $var=>$value) {
            if (in_array($var, $this->required_fields) or array_key_exists($var, $this->optional_fields)) {
                if (is_object($value) or is_array($value)) {
                    debugging("Incorrect property '$var' found when inserting grade object");
                } else {
                    $data->$var = $value;
                }
            }
        }
        return $data;
    }

    
    public function insert($source=null) {
        global $USER, $CFG, $DB;

        if (!empty($this->id)) {
            debugging("Grade object already exists!");
            return false;
        }

        $data = $this->get_record_data();

        $this->id = $DB->insert_record($this->table, $data);

                $this->update_from_db();

        $data = $this->get_record_data();

        if (empty($CFG->disablegradehistory)) {
            unset($data->timecreated);
            $data->action       = GRADE_HISTORY_INSERT;
            $data->oldid        = $this->id;
            $data->source       = $source;
            $data->timemodified = time();
            $data->loggeduser   = $USER->id;
            $DB->insert_record($this->table.'_history', $data);
        }

        $this->notify_changed(false);
        return $this->id;
    }

    
    public function update_from_db() {
        if (empty($this->id)) {
            debugging("The object could not be used in its state to retrieve a matching record from the DB, because its id field is not set.");
            return false;
        }
        global $DB;
        if (!$params = $DB->get_record($this->table, array('id' => $this->id))) {
            debugging("Object with this id:{$this->id} does not exist in table:{$this->table}, can not update from db!");
            return false;
        }

        grade_object::set_properties($this, $params);

        return true;
    }

    
    public static function set_properties(&$instance, $params) {
        $params = (array) $params;
        foreach ($params as $var => $value) {
            if (in_array($var, $instance->required_fields) or array_key_exists($var, $instance->optional_fields)) {
                $instance->$var = $value;
            }
        }
    }

    
    protected function notify_changed($deleted) {
    }

    
    function is_hidden() {
        return ($this->hidden == 1 or ($this->hidden != 0 and $this->hidden > time()));
    }

    
    function is_hiddenuntil() {
        return $this->hidden > 1;
    }

    
    function get_hidden() {
        return $this->hidden;
    }

    
    function set_hidden($hidden, $cascade=false) {
        $this->hidden = $hidden;
        $this->update();
    }

    
    public function can_control_visibility() {
        return true;
    }
}
