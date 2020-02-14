<?php




class mod_attendance_view_page_params extends mod_attendance_page_with_filter_controls {
    const MODE_THIS_COURSE  = 0;
    const MODE_ALL_COURSES  = 1;

    public $studentid;

    public $mode;

    public function  __construct() {
        $this->defaultview = ATT_VIEW_MONTHS;
    }

    public function get_significant_params() {
        $params = array();

        if (isset($this->studentid)) {
            $params['studentid'] = $this->studentid;
        }
        if ($this->mode != self::MODE_THIS_COURSE) {
            $params['mode'] = $this->mode;
        }

        return $params;
    }
}