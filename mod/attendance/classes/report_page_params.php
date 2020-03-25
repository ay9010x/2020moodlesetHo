<?php




class mod_attendance_report_page_params extends mod_attendance_page_with_filter_controls {
    public $group;
    public $sort;

    public function  __construct() {
        $this->selectortype = self::SELECTOR_GROUP;
    }

    public function init($cm) {
        parent::init($cm);

        if (!isset($this->group)) {
            $this->group = $this->get_current_sesstype() > 0 ? $this->get_current_sesstype() : 0;
        }
        if (!isset($this->sort)) {
            $this->sort = ATT_SORT_LASTNAME;
        }
    }

    public function get_significant_params() {
        $params = array();

        if ($this->sort != ATT_SORT_LASTNAME) {
            $params['sort'] = $this->sort;
        }

        return $params;
    }
}