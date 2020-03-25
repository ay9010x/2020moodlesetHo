<?php



namespace tool_lpmigrate\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
use renderable;


class renderer extends plugin_renderer_base {

    
    public function render_migrate_framework_results(migrate_framework_results $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('tool_lpmigrate/migrate_frameworks_results', $data);
    }
}
