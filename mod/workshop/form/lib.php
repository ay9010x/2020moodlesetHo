<?php



defined('MOODLE_INTERNAL') || die();


interface workshop_strategy {

    
    public function get_edit_strategy_form($actionurl=null);

    
    public function save_edit_strategy_form(stdclass $data);

    
    public function get_assessment_form(moodle_url $actionurl=null, $mode='preview', stdclass $assessment=null, $editable=true, $options=array());

    
    public function save_assessment(stdclass $assessment, stdclass $data);

    
    public function form_ready();

    
    public function get_dimensions_info();

    
    public function get_assessments_recordset($restrict=null);

    
    public static function scale_used($scaleid, $workshopid=null);

    
    public static function delete_instance($workshopid);
}
