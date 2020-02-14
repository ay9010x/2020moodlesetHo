<?php



namespace tool_log\helper;

defined('MOODLE_INTERNAL') || die();


trait reader {
    
    public function get_name() {
        if (get_string_manager()->string_exists('pluginname', $this->component)) {
            return get_string('pluginname', $this->component);
        }
        return $this->store;
    }

    
    public function get_description() {
        if (get_string_manager()->string_exists('pluginname_desc', $this->component)) {
            return get_string('pluginname_desc', $this->component);
        }
        return $this->store;
    }

    
    protected static function tweak_sort_by_id($sort) {
        if (empty($sort)) {
                        $sort = "id ASC";
        } else if (stripos($sort, 'timecreated') === false) {
            $sort .= ", id ASC";
        } else if (stripos($sort, 'timecreated DESC') !== false) {
            $sort .= ", id DESC";
        } else {
            $sort .= ", id ASC";
        }

        return $sort;
    }
}
