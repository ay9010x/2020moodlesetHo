<?php




class mod_attendance_manage_page_params extends mod_attendance_page_with_filter_controls {
    public function  __construct() {
        $this->selectortype = mod_attendance_page_with_filter_controls::SELECTOR_SESS_TYPE;
    }

    public function get_significant_params() {
        return array();
    }
}