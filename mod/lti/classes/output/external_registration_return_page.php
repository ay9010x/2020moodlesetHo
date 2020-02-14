<?php


namespace mod_lti\output;

require_once($CFG->dirroot.'/mod/lti/locallib.php');

use renderable;
use templatable;
use renderer_base;
use stdClass;


class external_registration_return_page implements renderable, templatable {

    
    public function export_for_template(renderer_base $output) {
        return new stdClass();
    }
}
