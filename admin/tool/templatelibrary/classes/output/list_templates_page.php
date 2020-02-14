<?php


namespace tool_templatelibrary\output;

use renderable;
use templatable;
use renderer_base;
use stdClass;
use core_plugin_manager;
use tool_templatelibrary\api;


class list_templates_page implements renderable, templatable {

    
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->allcomponents = array();
        $fulltemplatenames = api::list_templates();
        $pluginmanager = core_plugin_manager::instance();
        $components = array();

        foreach ($fulltemplatenames as $templatename) {
            list($component, $templatename) = explode('/', $templatename, 2);
            $components[$component] = 1;
        }

        $components = array_keys($components);
        foreach ($components as $component) {
            $info = new stdClass();
            $info->component = $component;
            if (strpos($component, 'core') === 0) {
                $info->name = get_string('coresubsystem', 'tool_templatelibrary', $component);
            } else {
                $info->name = $pluginmanager->plugin_name($component);
            }
            $data->allcomponents[] = $info;
        }

        return $data;
    }
}
