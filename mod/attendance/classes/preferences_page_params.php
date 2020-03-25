<?php




class mod_attendance_preferences_page_params {
    const ACTION_ADD              = 1;
    const ACTION_DELETE           = 2;
    const ACTION_HIDE             = 3;
    const ACTION_SHOW             = 4;
    const ACTION_SAVE             = 5;

    
    public $action;

    public $statusid;

    public $statusset;

    public function get_significant_params() {
        $params = array();

        if (isset($this->action)) {
            $params['action'] = $this->action;
        }
        if (isset($this->statusid)) {
            $params['statusid'] = $this->statusid;
        }
        if (isset($this->statusset)) {
            $params['statusset'] = $this->statusset;
        }

        return $params;
    }
}