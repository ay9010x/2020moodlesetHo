<?php


namespace mod_lti\output;

require_once($CFG->dirroot.'/mod/lti/locallib.php');

use moodle_url;
use renderable;
use templatable;
use renderer_base;
use stdClass;


class tool_configure_page implements renderable, templatable {
    
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();

        $url = new moodle_url('/mod/lti/typessettings.php', array('sesskey' => sesskey(), 'returnto' => 'toolconfigure'));
        $data->configuremanualurl = $url->out();
        $url = new moodle_url('/admin/settings.php?section=modsettinglti');
        $data->managetoolsurl = $url->out();
        $url = new moodle_url('/mod/lti/toolproxies.php');
        $data->managetoolproxiesurl = $url->out();

        return $data;
    }
}
