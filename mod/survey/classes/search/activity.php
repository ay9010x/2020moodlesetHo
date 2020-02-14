<?php



namespace mod_survey\search;

defined('MOODLE_INTERNAL') || die();


class activity extends \core_search\area\base_activity {

    
    public function get_recordset_by_timestamp($modifiedfrom = 0) {
        global $DB;
        $select = 'course != ? AND ' . static::MODIFIED_FIELD_NAME . ' >= ?';
        return $DB->get_recordset_select($this->get_module_name(), $select, array(0, $modifiedfrom),
                static::MODIFIED_FIELD_NAME . ' ASC');
    }

}
