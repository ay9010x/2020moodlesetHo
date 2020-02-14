<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/data/backup/moodle2/restore_data_stepslib.php'); 

class restore_data_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_data_activity_structure_step('data_structure', 'data.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('data', array(
                              'intro', 'singletemplate', 'listtemplate', 'listtemplateheader', 'listtemplatefooter',
                              'addtemplate', 'rsstemplate', 'rsstitletemplate', 'asearchtemplate'), 'data');
        $contents[] = new restore_decode_content('data_fields', array(
                              'description', 'param1', 'param2', 'param3',
                              'param4', 'param5', 'param6', 'param7',
                              'param8', 'param9', 'param10'), 'data_field');
        $contents[] = new restore_decode_content('data_content', array(
                              'content', 'content1', 'content2', 'content3', 'content4'));

        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('DATAVIEWBYID', '/mod/data/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('DATAVIEWBYD', '/mod/data/index.php?d=$1', 'data');
        $rules[] = new restore_decode_rule('DATAINDEX', '/mod/data/index.php?id=$1', 'course');
        $rules[] = new restore_decode_rule('DATAVIEWRECORD', '/mod/data/view.php?d=$1&amp;rid=$2', array('data', 'data_record'));

        return $rules;

    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('data', 'add', 'view.php?d={data}&rid={data_record}', '{data}');
        $rules[] = new restore_log_rule('data', 'update', 'view.php?d={data}&rid={data_record}', '{data}');
        $rules[] = new restore_log_rule('data', 'view', 'view.php?id={course_module}', '{data}');
        $rules[] = new restore_log_rule('data', 'record delete', 'view.php?id={course_module}', '{data}');
        $rules[] = new restore_log_rule('data', 'fields add', 'field.php?d={data}&mode=display&fid={data_field}', '{data_field}');
        $rules[] = new restore_log_rule('data', 'fields update', 'field.php?d={data}&mode=display&fid={data_field}', '{data_field}');
        $rules[] = new restore_log_rule('data', 'fields delete', 'field.php?d={data}', '[name]');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('data', 'view all', 'index.php?id={course}', null);

        return $rules;
    }

    
    public function get_comment_mapping_itemname($commentarea) {
        if ($commentarea == 'database_entry') {
            $itemname = 'data_record';
        } else {
            $itemname = parent::get_comment_mapping_itemname($commentarea);
        }
        return $itemname;
    }
}
