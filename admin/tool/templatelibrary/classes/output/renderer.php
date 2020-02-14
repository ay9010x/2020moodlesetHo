<?php


namespace tool_templatelibrary\output;

defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;


class renderer extends plugin_renderer_base {

    
    public function render_list_templates_page($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('tool_templatelibrary/list_templates_page', $data);
    }

}
