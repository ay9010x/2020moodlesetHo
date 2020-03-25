<?php



defined('MOODLE_INTERNAL') || die();

require_once('grade_object.php');


class grade_scale extends grade_object {
    
    public $table = 'scale';

    
    public $required_fields = array('id', 'courseid', 'userid', 'name', 'scale', 'description', 'descriptionformat', 'timemodified');

    
    public $courseid;

    
    public $userid;

    
    public $name;

    
    public $scale_items = array();

    
    public $scale;

    
    public $description;

    
    public static function fetch($params) {
        return grade_object::fetch_helper('scale', 'grade_scale', $params);
    }

    
    public static function fetch_all($params) {
        return grade_object::fetch_all_helper('scale', 'grade_scale', $params);
    }

    
    public function insert($source=null) {
        $this->timecreated = time();
        $this->timemodified = time();
        return parent::insert($source);
    }

    
    public function update($source=null) {
        $this->timemodified = time();
        return parent::update($source);
    }

    
    public function delete($source=null) {
        global $DB;
        if (parent::delete($source)) {
            $context = context_system::instance();
            $fs = get_file_storage();
            $files = $fs->get_area_files($context->id, 'grade', 'scale', $this->id);
            foreach ($files as $file) {
                $file->delete();
            }
            return true;
        }
        return false;
    }

    
    public function get_name() {
        return format_string($this->name);
    }

    
    public function load_items($items=NULL) {
        if (empty($items)) {
            $this->scale_items = explode(',', $this->scale);
        } elseif (is_array($items)) {
            $this->scale_items = $items;
        } else {
            $this->scale_items = explode(',', $items);
        }

                foreach ($this->scale_items as $key => $val) {
            $this->scale_items[$key] = trim($val);
        }

        return $this->scale_items;
    }

    
    public function compact_items($items=NULL) {
        if (empty($items)) {
            $this->scale = implode(',', $this->scale_items);
        } elseif (is_array($items)) {
            $this->scale = implode(',', $items);
        } else {
            $this->scale = $items;
        }

        return $this->scale;
    }

    
    public function get_nearest_item($grade) {
        global $DB;
                $scales_array = $DB->get_records('scale', array('id' => $this->id));
        $scale = $scales_array[$this->id];
        $scales = explode(",", $scale->scale);

                if ($grade < 1) {
            $grade = 1;
        }

        return $scales[$grade-1];
    }

    
    public static function fetch_all_global() {
        return grade_scale::fetch_all(array('courseid'=>0));
    }

    
    public static function fetch_all_local($courseid) {
        return grade_scale::fetch_all(array('courseid'=>$courseid));
    }

    
    public function is_last_global_scale() {
        return ($this->courseid == 0) && (count(self::fetch_all_global()) == 1);
    }

    
    public function can_delete() {
        return !$this->is_used() && !$this->is_last_global_scale();
    }

    
    public function is_used() {
        global $DB;
        global $CFG;

                $params = array($this->id);
        $sql = "SELECT COUNT(id) FROM {grade_items} WHERE scaleid = ? AND outcomeid IS NULL";
        if ($DB->count_records_sql($sql, $params)) {
            return true;
        }

                $sql = "SELECT COUNT(id) FROM {grade_outcomes} WHERE scaleid = ?";
        if ($DB->count_records_sql($sql, $params)) {
            return true;
        }

                if (\core_competency\api::is_scale_used_anywhere($this->id)) {
            return true;
        }

                $pluginsfunction = get_plugins_with_function('scale_used_anywhere');
        foreach ($pluginsfunction as $plugintype => $plugins) {
            foreach ($plugins as $pluginfunction) {
                if ($pluginfunction($this->id)) {
                    return true;
                }
            }
        }

        return false;
    }

    
    public function get_description() {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $systemcontext = context_system::instance();
        $options = new stdClass;
        $options->noclean = true;
        $description = file_rewrite_pluginfile_urls($this->description, 'pluginfile.php', $systemcontext->id, 'grade', 'scale', $this->id);
        return format_text($description, $this->descriptionformat, $options);
    }
}
