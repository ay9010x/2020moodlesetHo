<?php


namespace mod_lti\output;

defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;


class renderer extends plugin_renderer_base {

    
    public function render_tool_configure_page($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('mod_lti/tool_configure', $data);
    }

    
    public function render_external_registration_return_page($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('mod_lti/external_registration_return', $data);
    }
}
